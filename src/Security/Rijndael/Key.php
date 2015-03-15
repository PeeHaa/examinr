<?php
/**
 * Generates Rijndael keys
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Security
 * @subpackage Rijndael
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Security\Rijndael;

use Examinr\Security\StrengthException;

/**
 * Generates Rijndael keys
 *
 * @category   Examinr
 * @package    Security
 * @subpackage Rijndael
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Key
{
    /**
     * Generates a key
     *
     * @return string The key
     *
     * @throws \Examinr\Security\StrengthException When their could not be generated a sufficiently string key
     */
    public function generate()
    {
        $length = mcrypt_get_key_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB);
        $key    = openssl_random_pseudo_bytes($length, $crypto_strong);

        if (!$crypto_strong) {
            throw new StrengthException('Could not generate a sufficiently strong key.');
        }

        return $key;
    }
}
