<?php
/**
 * User class
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Auth
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Auth;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * User class
 *
 * @category   Examinr
 * @package    Auth
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class User
{
    /**
     * @var int List of roles in the system
     */
    const ROLE_GUEST = 0;
    const ROLE_USER  = 10;
    const ROLE_ADMIN = 20;

    /**
     * @var int The cost used to hash passwords
     */
    const PASSWORD_COST = 14;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface The session object
     */
    private $session;

    /**
     * Creates instance
     *
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session The session object
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Checks whether the user is logged in
     *
     * @return bool True when the user is logged in
     */
    public function isLoggedIn()
    {
        return $this->session->has('user');
    }

    /**
     * Checks whether the user is an admin
     *
     * @return bool True when the user is an admin
     */
    public function isAdmin()
    {
        return $this->getRole() === self::ROLE_ADMIN;
    }

    /**
     * Gets the rols of the user
     *
     * @return int The role of the user
     */
    private function getRole()
    {
        if (!$this->isLoggedIn()) {
            return self::ROLE_GUEST;
        }

        return self::ROLE_USER;
    }

    /**
     * Logs a user in
     *
     * @param string $email    The email address
     * @param string $password The password
     * @param array  $user     The user record
     *
     * @return bool True when the user successfully logged in
     */
    public function logIn($email, $password, array $user)
    {
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        $this->logInWithoutPassword($user);

        return true;
    }

    /**
     * Logs a user in with passwordless authentication
     *
     * @param array $user The user record
     *
     * @return bool True when the user successfully logged in
     */
    public function logInWithoutPassword(array $user)
    {
        $this->logOut();

        $this->session->set('user', $user);
    }

    /**
     * Checks whether the user's password needs to be rehashed
     *
     * @return bool True when the password needs to be rehashed
     */
    public function needsRehash()
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        return password_needs_rehash($this->session->get('user')['password'], PASSWORD_DEFAULT, ['cost' => self::PASSWORD_COST]);
    }

    /**
     * Rehashes the password of the user
     *
     * @param string $password The password to rehash
     *
     * @return string The hashed password
     */
    public function rehash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => self::PASSWORD_COST]);
    }

    /**
     * Logs the user out
     */
    public function logOut()
    {
        $this->session->invalidate();
    }

    /**
     * Magic getter
     *
     * Gets a property of the user
     *
     * @return mixed The property of the user if exists or a placeholder otherwise
     */
    public function __get($key)
    {
        $user = $this->session->get('user');

        if (array_key_exists($key, $user)) {
            return $user[$key];
        }

        return '{{' . $key . '}}';
    }
}
