<?php
/**
 * Security functions
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
 * A timing safe equals comparison
 *
 * To prevent leaking length information, it is important
 * that user input is always used as the second parameter.
 *
 * @author Anthony Ferrara <https://github.com/ircmaxell>
 * @link   http://stackoverflow.com/a/17266448/508666
 *
 * @param string $safe The internal (safe) value to be checked
 * @param string $user The user submitted (unsafe) value
 *
 * @return boolean True if the two strings are identical.
 */
function compare($safe, $user) {
    $safe .= chr(0);
    $user .= chr(0);

    $safeLen = strlen($safe);
    $userLen = strlen($user);

    $result = $safeLen - $userLen;

    for ($i = 0; $i < $userLen; $i++) {
        $result |= (ord($safe[$i % $safeLen]) ^ ord($user[$i]));
    }

    return $result === 0;
}
