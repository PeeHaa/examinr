<?php
/**
 * Cookie interface
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
 */
namespace Examinr\Storage\Http;

/**
 * Cookie interface
 *
 * @category   Examinr
 * @package    Storage
 * @subpackage Http
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
interface Cookie
{
    /**
     * Checks whether the cookie exists
     *
     * @return bool True when it exists
     */
    public function exists();

    /**
     * Gets the cookie data
     *
     * @return mixed The parsed cookie data
     */
    public function get();

    /**
     * Sets the cookie
     *
     * @param mixed $data       The cookie data
     * @param int   $expiration The expiration timestamp
     */
    public function set($data, $exipration);

    /**
     * Invalidate the cookie
     */
    public function invalidate();
}
