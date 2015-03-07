<?php
/**
 * Bootstrap the project
 *
 * PHP version 5.5
 *
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr;

use Examinr\Network\Http\Request;
use Examinr\Auth\User;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Setup the project autoloader
 */
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Setup the environment
 */
require_once __DIR__ . '/init.deployment.php';

/**
 * Prevent further execution when on CLI
 */
if (php_sapi_name() === 'cli') {
    return;
}

/**
 * Setup the request object
 */
$request = Request::createFromGlobals();

/**
 * Setup the session
 */
session_set_cookie_params (0, '/', '.' . $request->getHost(), $request->isSecure(), true);
$session = new Session();

/**
 * Setup the user object
 */
$user = new User($session);
