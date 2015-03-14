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
use Examinr\Security\CsrfToken;

use Examinr\Storage\Http\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;
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
$pdoSessionStorage = new PdoSessionHandler($dbConnection, [
    'db_id_col'       => 'id',
    'db_data_col'     => 'data',
    'db_lifetime_col' => 'lifetime',
    'db_time_col'     => 'time',
]);
$pdoSessionBridge  = new PhpBridgeSessionStorage($pdoSessionStorage);
$session           = new Session($pdoSessionBridge);
session_start();

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
 * Setup the CSRF token
 */
$csrfToken = new CsrfToken($tokenGenerator, $session);

/**
 * Setup the mailer
 */
$swiftTransport = \Swift_MailTransport::newInstance();
$swiftMailer    = \Swift_Mailer::newInstance($swiftTransport);

/**
 * Setup the DI
 */
$injector = new Provider();
$injector->define('Examinr\\Mail\\Mailer', [':mailer' => $swiftMailer]);
$injector->define('Examinr\\Form\\Builder', ['template' => 'Examinr\\Presentation\\Template\\Html']);
$injector->define('Examinr\\Presentation\\Template\\Html', [':basePage' => '/page.phtml']);
$injector->alias('Examinr\\Presentation\\Theme\\Loader', get_class($theme));
$injector->share($request);
$injector->share($theme);
$injector->share($tokenGenerator);
$injector->alias('Examinr\\Security\\Token', get_class($csrfToken));
$injector->share($csrfToken);
$injector->share($user);
$injector->share($dbConnection);
$injector->alias('Symfony\\Component\\HttpFoundation\\Session\\SessionInterface', get_class($session));
$injector->share($session);
$injector->alias('Examinr\\I18n\\Translator', get_class($translator));
$injector->share($translator);
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
