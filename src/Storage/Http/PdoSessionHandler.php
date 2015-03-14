<?php
/**
 * HTTP session storage pdo
 *
 * This is a thin layer on top of Symfony's session pdo class to be able to bind a user to a session.
 * This is a dirty hack, but for the moment I don't see a better way which is easily works.
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Storage
 * @subpackage Http
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 *
 * @todo       Swap this entire clusterfuck based on Symfony's HTTP foundation to something sane
 */
namespace Examinr\Storage\Http;

use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler as BaseHandler;

/**
 * HTTP session storage pdo
 *
 * @category   Examinr
 * @package    Storage
 * @subpackage Http
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class PdoSessionHandler extends BaseHandler
{
    /**
     * @var \PDO|null PDO instance or null when not connected yet
     */
    private $pdo;

    /**
     * @var string|null|false DSN string or null for session.save_path or false when lazy connection disabled
     */
    private $dsn = false;

    /**
     * @var string Database driver
     */
    private $driver;

    /**
     * @var string Table name
     */
    private $table = 'sessions';

    /**
     * @var string Column for session id
     */
    private $idCol = 'sess_id';

    /**
     * @var string Column for session data
     */
    private $dataCol = 'sess_data';

    /**
     * @var string Column for lifetime
     */
    private $lifetimeCol = 'sess_lifetime';

    /**
     * @var string Column for timestamp
     */
    private $timeCol = 'sess_time';

    /**
     * @var string COlumn for the user id
     */
    private $userCol = 'user_id';

    /**
     * @var string Username when lazy-connect
     */
    private $username = '';

    /**
     * @var string Password when lazy-connect
     */
    private $password = '';

    /**
     * @var array Connection options when lazy-connect
     */
    private $connectionOptions = array();

    /**
     * @var int The strategy for locking, see constants
     */
    private $lockMode = parent::LOCK_TRANSACTIONAL;

    /**
     * It's an array to support multiple reads before closing which is manual, non-standard usage
     *
     * @var \PDOStatement[] An array of statements to release advisory locks
     */
    private $unlockStatements = array();

    /**
     * @var bool True when the current session exists but expired according to session.gc_maxlifetime
     */
    private $sessionExpired = false;

    /**
     * @var bool Whether a transaction is active
     */
    private $inTransaction = false;

    /**
     * @var bool Whether gc() has been called
     */
    private $gcCalled = false;

    /**
     * Constructor.
     *
     * You can either pass an existing database connection as PDO instance or
     * pass a DSN string that will be used to lazy-connect to the database
     * when the session is actually used. Furthermore it's possible to pass null
     * which will then use the session.save_path ini setting as PDO DSN parameter.
     *
     * List of available options:
     *  * db_table: The name of the table [default: sessions]
     *  * db_id_col: The column where to store the session id [default: sess_id]
     *  * db_data_col: The column where to store the session data [default: sess_data]
     *  * db_lifetime_col: The column where to store the lifetime [default: sess_lifetime]
     *  * db_time_col: The column where to store the timestamp [default: sess_time]
     *  * db_username: The username when lazy-connect [default: '']
     *  * db_password: The password when lazy-connect [default: '']
     *  * db_connection_options: An array of driver-specific connection options [default: array()]
     *  * lock_mode: The strategy for locking, see constants [default: LOCK_TRANSACTIONAL]
     *
     * @param \PDO|string|null $pdoOrDsn A \PDO instance or DSN string or null
     * @param array            $options  An associative array of options
     *
     * @throws \InvalidArgumentException When PDO error mode is not PDO::ERRMODE_EXCEPTION
     */
    public function __construct($pdoOrDsn = null, array $options = array())
    {
        if ($pdoOrDsn instanceof \PDO) {
            if (\PDO::ERRMODE_EXCEPTION !== $pdoOrDsn->getAttribute(\PDO::ATTR_ERRMODE)) {
                throw new \InvalidArgumentException(sprintf('"%s" requires PDO error mode attribute be set to throw Exceptions (i.e. $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION))', __CLASS__));
            }

            $this->pdo = $pdoOrDsn;
            $this->driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } else {
            $this->dsn = $pdoOrDsn;
        }

        $this->table = isset($options['db_table']) ? $options['db_table'] : $this->table;
        $this->idCol = isset($options['db_id_col']) ? $options['db_id_col'] : $this->idCol;
        $this->dataCol = isset($options['db_data_col']) ? $options['db_data_col'] : $this->dataCol;
        $this->lifetimeCol = isset($options['db_lifetime_col']) ? $options['db_lifetime_col'] : $this->lifetimeCol;
        $this->timeCol = isset($options['db_time_col']) ? $options['db_time_col'] : $this->timeCol;
        $this->username = isset($options['db_username']) ? $options['db_username'] : $this->username;
        $this->password = isset($options['db_password']) ? $options['db_password'] : $this->password;
        $this->connectionOptions = isset($options['db_connection_options']) ? $options['db_connection_options'] : $this->connectionOptions;
        $this->lockMode = isset($options['lock_mode']) ? $options['lock_mode'] : $this->lockMode;

        parent::__construct($pdoOrDsn, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data)
    {
        $maxlifetime = (int) ini_get('session.gc_maxlifetime');
        $userId      = isset($_SESSION['_sf2_attributes']['user']) ? $_SESSION['_sf2_attributes']['user']['id'] : null;

        try {
            // We use a single MERGE SQL query when supported by the database.
            $mergeSql = $this->getMergeSql();

            if (null !== $mergeSql) {
                $mergeStmt = $this->pdo->prepare($mergeSql);
                $mergeStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
                $mergeStmt->bindParam(':data', $data, \PDO::PARAM_LOB);
                $mergeStmt->bindParam(':lifetime', $maxlifetime, \PDO::PARAM_INT);
                $mergeStmt->bindValue(':time', time(), \PDO::PARAM_INT);
                $mergeStmt->bindValue(':userId', $userId, \PDO::PARAM_INT);
                $mergeStmt->execute();

                return true;
            }

            $updateStmt = $this->pdo->prepare(
                "UPDATE $this->table SET $this->dataCol = :data, $this->lifetimeCol = :lifetime, $this->timeCol = :time, $this->userCol = :userId WHERE $this->idCol = :id"
            );
            $updateStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
            $updateStmt->bindParam(':data', $data, \PDO::PARAM_LOB);
            $updateStmt->bindParam(':lifetime', $maxlifetime, \PDO::PARAM_INT);
            $updateStmt->bindValue(':time', time(), \PDO::PARAM_INT);
            $updateStmt->bindValue(':userId', $userId, \PDO::PARAM_INT);
            $updateStmt->execute();

            // When MERGE is not supported, like in Postgres, we have to use this approach that can result in
            // duplicate key errors when the same session is written simultaneously (given the LOCK_NONE behavior).
            // We can just catch such an error and re-execute the update. This is similar to a serializable
            // transaction with retry logic on serialization failures but without the overhead and without possible
            // false positives due to longer gap locking.
            if (!$updateStmt->rowCount()) {
                try {
                    $insertStmt = $this->pdo->prepare(
                        "INSERT INTO $this->table ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol, $this->userCol) VALUES (:id, :data, :lifetime, :time, :userId)"
                    );
                    $insertStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
                    $insertStmt->bindParam(':data', $data, \PDO::PARAM_LOB);
                    $insertStmt->bindParam(':lifetime', $maxlifetime, \PDO::PARAM_INT);
                    $insertStmt->bindValue(':time', time(), \PDO::PARAM_INT);
                    $insertStmt->bindValue(':userId', $userId, \PDO::PARAM_INT);
                    $insertStmt->execute();
                } catch (\PDOException $e) {
                    // Handle integrity violation SQLSTATE 23000 (or a subclass like 23505 in Postgres) for duplicate keys
                    if (0 === strpos($e->getCode(), '23')) {
                        $updateStmt->execute();
                    } else {
                        throw $e;
                    }
                }
            }
        } catch (\PDOException $e) {
            $this->rollback();

            throw $e;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    private function beginTransaction()
    {
        if (!$this->inTransaction) {
            if ('sqlite' === $this->driver) {
                $this->pdo->exec('BEGIN IMMEDIATE TRANSACTION');
            } else {
                if ('mysql' === $this->driver) {
                    $this->pdo->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                }
                $this->pdo->beginTransaction();
            }
            $this->inTransaction = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    private function commit()
    {
        if ($this->inTransaction) {
            try {
                // commit read-write transaction which also releases the lock
                if ('sqlite' === $this->driver) {
                    $this->pdo->exec('COMMIT');
                } else {
                    $this->pdo->commit();
                }
                $this->inTransaction = false;
            } catch (\PDOException $e) {
                $this->rollback();

                throw $e;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    private function rollback()
    {
        // We only need to rollback if we are in a transaction. Otherwise the resulting
        // error would hide the real problem why rollback was called. We might not be
        // in a transaction when not using the transactional locking behavior or when
        // two callbacks (e.g. destroy and write) are invoked that both fail.
        if ($this->inTransaction) {
            if ('sqlite' === $this->driver) {
                $this->pdo->exec('ROLLBACK');
            } else {
                $this->pdo->rollback();
            }
            $this->inTransaction = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    private function doRead($sessionId)
    {
        $this->sessionExpired = false;

        //$userId = isset($_SESSION['_sf2_attributes']['user']) ? $_SESSION['_sf2_attributes']['user']['id'] : null;

        if (self::LOCK_ADVISORY === $this->lockMode) {
            $this->unlockStatements[] = $this->doAdvisoryLock($sessionId);
        }

        $selectSql = $this->getSelectSql();
        $selectStmt = $this->pdo->prepare($selectSql);
        $selectStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
        $selectStmt->execute();

        $sessionRows = $selectStmt->fetchAll(\PDO::FETCH_NUM);

        if ($sessionRows) {
            if ($sessionRows[0][1] + $sessionRows[0][2] < time()) {
                $this->sessionExpired = true;

                return '';
            }

            return is_resource($sessionRows[0][0]) ? stream_get_contents($sessionRows[0][0]) : $sessionRows[0][0];
        }

        if (self::LOCK_TRANSACTIONAL === $this->lockMode && 'sqlite' !== $this->driver) {
            // Exclusive-reading of non-existent rows does not block, so we need to do an insert to block
            // until other connections to the session are committed.
            try {
                $insertStmt = $this->pdo->prepare(
                    "INSERT INTO $this->table ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (:id, :data, :lifetime, :time)"
                );
                $insertStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
                $insertStmt->bindValue(':data', '', \PDO::PARAM_LOB);
                $insertStmt->bindValue(':lifetime', 0, \PDO::PARAM_INT);
                $insertStmt->bindValue(':time', time(), \PDO::PARAM_INT);
                $insertStmt->execute();
            } catch (\PDOException $e) {
                // Catch duplicate key error because other connection created the session already.
                // It would only not be the case when the other connection destroyed the session.
                if (0 === strpos($e->getCode(), '23')) {
                    // Retrieve finished session data written by concurrent connection. SELECT
                    // FOR UPDATE is necessary to avoid deadlock of connection that starts reading
                    // before we write (transform intention to real lock).
                    $selectStmt->execute();
                    $sessionRows = $selectStmt->fetchAll(\PDO::FETCH_NUM);

                    if ($sessionRows) {
                        return is_resource($sessionRows[0][0]) ? stream_get_contents($sessionRows[0][0]) : $sessionRows[0][0];
                    }

                    return '';
                }

                throw $e;
            }
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    private function doAdvisoryLock($sessionId)
    {
        switch ($this->driver) {
            case 'mysql':
                // should we handle the return value? 0 on timeout, null on error
                // we use a timeout of 50 seconds which is also the default for innodb_lock_wait_timeout
                $stmt = $this->pdo->prepare('SELECT GET_LOCK(:key, 50)');
                $stmt->bindValue(':key', $sessionId, \PDO::PARAM_STR);
                $stmt->execute();

                $releaseStmt = $this->pdo->prepare('DO RELEASE_LOCK(:key)');
                $releaseStmt->bindValue(':key', $sessionId, \PDO::PARAM_STR);

                return $releaseStmt;
            case 'pgsql':
                // Obtaining an exclusive session level advisory lock requires an integer key.
                // So we convert the HEX representation of the session id to an integer.
                // Since integers are signed, we have to skip one hex char to fit in the range.
                if (4 === PHP_INT_SIZE) {
                    $sessionInt1 = hexdec(substr($sessionId, 0, 7));
                    $sessionInt2 = hexdec(substr($sessionId, 7, 7));

                    $stmt = $this->pdo->prepare('SELECT pg_advisory_lock(:key1, :key2)');
                    $stmt->bindValue(':key1', $sessionInt1, \PDO::PARAM_INT);
                    $stmt->bindValue(':key2', $sessionInt2, \PDO::PARAM_INT);
                    $stmt->execute();

                    $releaseStmt = $this->pdo->prepare('SELECT pg_advisory_unlock(:key1, :key2)');
                    $releaseStmt->bindValue(':key1', $sessionInt1, \PDO::PARAM_INT);
                    $releaseStmt->bindValue(':key2', $sessionInt2, \PDO::PARAM_INT);
                } else {
                    $sessionBigInt = hexdec(substr($sessionId, 0, 15));

                    $stmt = $this->pdo->prepare('SELECT pg_advisory_lock(:key)');
                    $stmt->bindValue(':key', $sessionBigInt, \PDO::PARAM_INT);
                    $stmt->execute();

                    $releaseStmt = $this->pdo->prepare('SELECT pg_advisory_unlock(:key)');
                    $releaseStmt->bindValue(':key', $sessionBigInt, \PDO::PARAM_INT);
                }

                return $releaseStmt;
            case 'sqlite':
                throw new \DomainException('SQLite does not support advisory locks.');
            default:
                throw new \DomainException(sprintf('Advisory locks are currently not implemented for PDO driver "%s".', $this->driver));
        }
    }

    /**
     * {@inheritdoc}
     */
    private function getSelectSql()
    {
        if (self::LOCK_TRANSACTIONAL === $this->lockMode) {
            $this->beginTransaction();

            switch ($this->driver) {
                case 'mysql':
                case 'oci':
                case 'pgsql':
                    return "SELECT $this->dataCol, $this->lifetimeCol, $this->timeCol, $this->userCol FROM $this->table WHERE $this->idCol = :id FOR UPDATE";
                case 'sqlsrv':
                    return "SELECT $this->dataCol, $this->lifetimeCol, $this->timeCol, $this->userCol FROM $this->table WITH (UPDLOCK, ROWLOCK) WHERE $this->idCol = :id";
                case 'sqlite':
                    // we already locked when starting transaction
                    break;
                default:
                    throw new \DomainException(sprintf('Transactional locks are currently not implemented for PDO driver "%s".', $this->driver));
            }
        }

        return "SELECT $this->dataCol, $this->lifetimeCol, $this->timeCol, $this->userCol FROM $this->table WHERE $this->idCol = :id";
    }

    /**
     * {@inheritdoc}
     */
    private function getMergeSql()
    {
        switch ($this->driver) {
            case 'mysql':
                return "INSERT INTO $this->table ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol, $this->userCol) VALUES (:id, :data, :lifetime, :time, :userId) ".
                    "ON DUPLICATE KEY UPDATE $this->dataCol = VALUES($this->dataCol), $this->lifetimeCol = VALUES($this->lifetimeCol), $this->timeCol = VALUES($this->timeCol), $this->userCol = VALUES($this->userCol)";
            case 'oci':
                // DUAL is Oracle specific dummy table
                return "MERGE INTO $this->table USING DUAL ON ($this->idCol = :id) ".
                    "WHEN NOT MATCHED THEN INSERT ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol, $this->userCol) VALUES (:id, :data, :lifetime, :time, :userId) ".
                    "WHEN MATCHED THEN UPDATE SET $this->dataCol = :data, $this->lifetimeCol = :lifetime, $this->timeCol = :time, $this->userCol = :userId";
            case 'sqlsrv' === $this->driver && version_compare($this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION), '10', '>='):
                // MERGE is only available since SQL Server 2008 and must be terminated by semicolon
                // It also requires HOLDLOCK according to http://weblogs.sqlteam.com/dang/archive/2009/01/31/UPSERT-Race-Condition-With-MERGE.aspx
                return "MERGE INTO $this->table WITH (HOLDLOCK) USING (SELECT 1 AS dummy) AS src ON ($this->idCol = :id) ".
                    "WHEN NOT MATCHED THEN INSERT ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol, $this->userCol) VALUES (:id, :data, :lifetime, :time, :userId) ".
                    "WHEN MATCHED THEN UPDATE SET $this->dataCol = :data, $this->lifetimeCol = :lifetime, $this->timeCol = :time, $this->userCol = :userId;";
            case 'sqlite':
                return "INSERT OR REPLACE INTO $this->table ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol, $this->userCol) VALUES (:id, :data, :lifetime, :time, :userId)";
        }
    }
}
