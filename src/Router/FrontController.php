<?php
/**
 * The application's front controller
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Router
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Router;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Auryn\Provider;
use Symfony\Component\HttpFoundation\Request;
use FastRoute\Dispatcher;

/**
 * The application's front controller
 *
 * @category   Examinr
 * @package    Router
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class FrontController
{
    /**
     * @var \Examinr\Router\Router The router
     */
    private $router;

    /**
     * @var \Symfony\Component\HttpFoundation\Response The HTTP response
     */
    private $response;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session The session handler
     */
    private $session;

    /**
     * Creates instance
     *
     * @param \Examinr\Router\Router                            $router   The router
     * @param \Symfony\Component\HttpFoundation\Response        $response The HTTP response
     * @param \Symfony\Component\HttpFoundation\Session\Session $session  The session handler
     * @param \Auryn\Provider                                   $executor The DI executor
     */
    public function __construct(Router $router, Response $response, Session $session, Executor $executor)
    {
        $this->router   = $router;
        $this->response = $response;
        $this->session  = $session;
        $this->executor = $executor;
    }

    /**
     * Runs the application
     *
     * This method gets the correct route for the current request and runs the callback of the route
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The current request
     */
    public function run(Request $request)
    {
        $dispatcher = $this->router->getDispatcher();
        $routeInfo  = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $routeInfo = $this->getNotFoundRoute($dispatcher);
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:
                $routeInfo = $this->runMethodNotAllowed($dispatcher);
                break;

            case Dispatcher::FOUND:
                break;
        }

        $response = $this->runRoute($routeInfo);

        $response->send();
    }

    /**
     * Runs a route
     *
     * @param array $routeInfo The info of the active route
     *
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response
     */
    private function runRoute(array $routeInfo)
    {
        list($_, $callback, $vars) = $routeInfo;

        $controller = new $callback[0]($this->response, $this->session);

        return $this->executor->execute([$controller, $callback[1]], $vars);
    }

    /**
     * Gets the "not found (404)" route
     *
     * @param \FastRoute\Dispatcher $dispatcher The request dispatcher
     *
     * @return array The route
     */
    private function getNotFoundRoute(Dispatcher $dispatcher)
    {
        return $dispatcher->dispatch('GET', '/not-found');
    }

    /**
     * Gets the "method not allowed (405)" route
     *
     * @param \FastRoute\Dispatcher $dispatcher The request dispatcher
     *
     * @return array The route
     */
    private function runMethodNotAllowed(Dispatcher $dispatcher)
    {
        return $dispatcher->dispatch('GET', '/method-not-allowed');
    }
}
