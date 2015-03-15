<?php
/**
 * User storage
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Storage
 * @subpackage MySql
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Storage\MySql;

use Examinr\Storage\Sql\Sql;
use Examinr\Form\Implementation\EditUser as EditUserForm;
use Examinr\Form\Implementation\AddUser as AddUserForm;

/**
 * User storage
 *
 * @category   Examinr
 * @package    Storage
 * @subpackage MySql
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class User implements Sql
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
     * Gets the overview of users in the system
     *
     * @return array List of users in the system
     */
    public function getOverview()
    {
        $query = 'SELECT id, name, email';
        $query.= ' FROM users';
        $query.= ' ORDER BY name ASC';

        $stmt = $this->dbConnection->query($query);

        return $stmt->fetchAll();
    }

    /**
     * Adds a user
     *
     * @param \Examinr\Form\Implementation\AddUser $form     The form
     * @param string                               $password The hashed password
     */
    public function add(AddUserForm $form, $password)
    {
        $query = 'INSERT INTO users';
        $query.= ' (name, email, password)';
        $query.= ' VALUES';
        $query.= ' (:name, :email, :password)';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'name'     => $form['name']->getValue(),
            'email'    => $form['email']->getValue(),
            'password' => $password,
        ]);
    }

    /**
     * Updates user info
     *
     * @param int                                   $id       The id of the user to update
     * @param \Examinr\Form\Implementation\EditUser $form     The form
     * @param string                                $password The password when changed
     */
    public function update($id, EditUserForm $form, $password = null)
    {
        $query = 'UPDATE users';
        $query.= ' SET name = :name, email = :email';

        if ($password) {
            $query .= ', password = :password';
        }

        $query.= ' WHERE id = :id';

        $stmt = $this->dbConnection->prepare($query);

        $params = [
            'id'      => $id,
            'name'    => $form['name']->getValue(),
            'email'   => $form['email']->getValue(),
        ];

        if ($password) {
            $params['password'] = $password;
        }

        $stmt->execute($params);
    }

    /**
     * Gets the authentication log of the user
     *
     * @param int $userId The user id
     *
     * @return array The authentication log
     */
    public function getAuthLog($userId)
    {
        $query = 'SELECT id, status, type, ip, timestamp';
        $query.= ' FROM auth_log';
        $query.= ' WHERE user_id = :userId';
        $query.= ' ORDER BY id DESC';
        $query.= ' LIMIT 0, 100';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'userId' => $userId,
        ]);

        return $stmt->fetchAll();
    }
}
