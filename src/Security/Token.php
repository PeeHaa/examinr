<?php
/**
 * Interface for token handlers
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Security
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Security;

/**
 * Interface for token handlers
 *
 * @category   Examinr
 * @package    Security
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
interface Token
{
    /**
     * Gets the token
     *
     * @return string The token
     */
    public function get();

    /**
     * Generates token
     */
    public function generate();

    /**
     * Validates a supplied token
     *
     * @param string $token The supplied token
     *
     * @return bool True when the token is valid
     */
    public function validate($token);
}
