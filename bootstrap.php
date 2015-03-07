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
use Examinr\Router\Router;

use Symfony\Component\HttpFoundation\Session\Session;

use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as RouteParser;
use FastRoute\DataGenerator\GroupCountBased as RouteDataGenerator;
use FastRoute\Dispatcher\GroupCountBased as RouteDispatcher;

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

/**
 * Setup the router
 */
$cacheFile      = $user->isLoggedIn() ? 'routes-logged-in.php' : 'routes.php';
$routeCollector = new RouteCollector(new RouteParser(), new RouteDataGenerator());
$router         = new Router($routeCollector, function($dispatchData) {
    return new RouteDispatcher($dispatchData);
}, __DIR__ . '/cache/' . $cacheFile, $reloadRoutes);
