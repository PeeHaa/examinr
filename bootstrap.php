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
use Examinr\Presentation\Theme\Theme;
use Examinr\I18n\FileTranslator;
use Examinr\Router\FrontController;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;

use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as RouteParser;
use FastRoute\DataGenerator\GroupCountBased as RouteDataGenerator;
use FastRoute\Dispatcher\GroupCountBased as RouteDispatcher;

use RandomLib\Factory as RandomFactory;
use SecurityLib\Strength;

use Auryn\Provider;

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

/**
 * Load routes
 */
require_once __DIR__ . '/routes.php';

/**
 * Setup the theme
 */
$theme = new Theme(__DIR__ . '/themes', 'Default');

/**
 * Setup i18n
 */
$translator = new FileTranslator(__DIR__ . '/texts', 'en_US');

/**
 * Setup the random generator
 */
$randomFactory  = new RandomFactory();
$tokenGenerator = $randomFactory->getGenerator(new Strength(Strength::MEDIUM));

/**
 * Setup the DI
 */
$injector = new Provider();

// setup the templates
$injector->define('Examinr\\Presentation\\Template\\Html', [':basePage' => '/page.phtml']);

// setup the theme loader
$injector->alias('Examinr\\Presentation\\Theme\\Loader', get_class($theme));
$injector->share($theme);

// setup random generator
$injector->share($tokenGenerator);

// setup session
$injector->alias('Symfony\\Component\\HttpFoundation\\Session\\SessionInterface', get_class($session));
$injector->share($session);

// setup translator
$injector->alias('Examinr\\I18n\\Translator', get_class($translator));
$injector->share($translator);

// setup the CSRF token
$injector->alias('Examinr\\Security\\Token', 'Examinr\\Security\\CsrfToken');
$injector->share($theme);

/**
 * Setup the front controller
 */
$frontController = new FrontController($router, new Response(), $session, $injector);

/**
 * Run the application
 */
$frontController->run($request);
