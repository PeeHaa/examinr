<?php
/**
 * Auth storage
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Storage
 * @subpackage Sql
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Storage\MySql;

use Examinr\Storage\Sql\Sql;
use Examinr\Security\StrengthException;

/**
 * Auth storage
 *
 * @category   Examinr
 * @package    Storage
 * @subpackage Sql
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Auth implements Sql
{
    /**
     * @var \PDO The database connection
     */
    private $dbConnection;

    /**
     * Creates instance
     *
     * @param \PDO $dbConnection The database connection
     */
    public function __construct(\PDO $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * Gets a user by email address
     *
     * @param string $email The email address of the user
     *
     * @return array Containing the info of the user
     */
    public function getByEmail($email)
    {
        $query = 'SELECT id, email, password, name';
        $query.= ' FROM users';
        $query.= ' WHERE email = :email';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'email' => $email,
        ]);

        return $stmt->fetch();
    }

    /**
     * Gets a user by id
     *
     * @param int $id The id of the user
     *
     * @return array Containing the info of the user
     */
    public function getById($id)
    {
        $query = 'SELECT id, email, password, name';
        $query.= ' FROM users';
        $query.= ' WHERE id = :id';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->fetch();
    }

    /**
     * Updates the password of a user
     *
     * @param int    $id       The id of the user
     * @param string $password The new password of the user
     */
    public function updatePasswordById($id, $password)
    {
        $query = 'UPDATE users';
        $query.= ' SET password = :password';
        $query.= ' WHERE id = :id';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'id'       => $id,
            'password' => $password,
        ]);
    }

    /**
     * Sets a password reset token
     *
     * @param int $id The user id to generate a password reset token for
     *
     * @return string The token
     */
    public function setPasswordResetToken($id)
    {
        $this->removeOldToken($id);

        $datetime = new \DateTime();

        $query = 'INSERT INTO password_resets';
        $query.= ' (user_id, timestamp, token)';
        $query.= ' VALUES';
        $query.= ' (:user_id, :timestamp, :token)';

        $token = $this->generateToken();

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'user_id'   => $id,
            'timestamp' => $datetime->format('Y-m-d H:i:s'),
            'token'     => $token,
        ]);

        return $token;
    }

    /**
     * Remove old password reset token by user id
     *
     * @param int $user_id The user id
     */
    private function removeOldToken($user_id)
    {
        $stmt = $this->dbConnection->prepare('DELETE FROM password_resets WHERE user_id = :user_id');
        $stmt->execute([
            'user_id' => $user_id,
        ]);
    }

    /**
     * Generates a cryptographically secure token
     *
     * @return string The newly generated token
     *
     * @throws \Examinr\Security\StrengthException When the generated token is too weak
     */
    private function generateToken()
    {
        $token = openssl_random_pseudo_bytes(32, $strong);

        if (!$strong) {
            throw new StrengthException('Could not generate a sufficiently strong password reset token');
        }

        return str_replace(['/', '+', '='], '', base64_encode($token));
    }

    /**
     * Checks whether the token is valid
     *
     * @param string $token The token to check
     *
     * @return bool true when the token is valid
     */
    public function isTokenValid($token)
    {
        $stmt = $this->dbConnection->prepare('SELECT COUNT(id) FROM password_resets WHERE token = :token');
        $stmt->execute([
            'token' => $token,
        ]);

        return (bool) $stmt->fetchColumn(0);
    }

    /**
     * Updates a user's password by a password reset token
     *
     * @param string $token    The reset token
     * @param string $password The new (hashed) password
     */
    public function updatePasswordByToken($token, $password)
    {
        $query = 'SELECT user_id FROM password_resets WHERE token = :token';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'token' => $token
        ]);

        $user_id = $stmt->fetchColumn(0);

        $query = 'UPDATE users SET password = :password WHERE id = :id';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'id'       => $user_id,
            'password' => $password,
        ]);
    }

    /**
     * Adds the remember me token to the database
     *
     * @param int    $userId      The id of the user for which to store the tokens
     * @param string $seriesToken The random series token
     * @param string $token       The random token
     */
    public function rememberMe($userId, $seriesToken, $token)
    {
        $query = 'DELETE FROM remember_me';
        $query.= ' WHERE user_id = :userId';
        $query.= ' AND series_token = :seriesToken';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'userId'      => $userId,
            'seriesToken' => $seriesToken,
        ]);

        $query = 'INSERT INTO remember_me';
        $query.= ' (user_id, series_token, token)';
        $query.= ' VALUES';
        $query.= ' (:userId, :seriesToken, :token)';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'userId'      => $userId,
            'seriesToken' => $seriesToken,
            'token'       => $token,
        ]);
    }

    /**
     * Adds the remember me token to the database
     *
     * @param int    $userId      The id of the user for which to store the tokens
     * @param string $seriesToken The random series token
     */
    public function getRememberMe($userId, $seriesToken)
    {
        $query = 'SELECT id, user_id, series_token, token';
        $query.= ' FROM remember_me';
        $query.= ' WHERE user_id = :userId';
        $query.= ' AND series_token = :seriesToken';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'userId'      => $userId,
            'seriesToken' => $seriesToken,
        ]);

        return $stmt->fetch();
    }

    /**
     * Invalidates all user state including cookies and active sessions
     *
     * @param int $userId The id of the user to invalidate
     */
    public function invalidateUser($userId)
    {
        $query = 'DELETE FROM sessions';
        $query.= ' WHERE user_id = :userId';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'userId' => $userId,
        ]);

        $query = 'DELETE FROM remember_me';
        $query.= ' WHERE user_id = :userId';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'userId' => $userId,
        ]);
    }

    /**
     * Logs an authentication attempt
     *
     * @param int    $userId  The id of the user
     * @param bool   $success Whether the authentication succeeded
     * @param string $type    The type of the authentication (either standard or cookie)
     * @param string $ip      The originating IP address
     */
    public function logAuthentication($userId, $success, $type, $ip)
    {
        $query = 'INSERT INTO auth_log';
        $query.= ' (user_id, status, type, ip)';
        $query.= ' VALUES';
        $query.= ' (:userId, :status, :type, :ip)';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'userId' => $userId,
            'status' => $success ? 'success' : 'failed',
            'type'   => $type,
            'ip'     => $ip,
        ]);
    }
}
