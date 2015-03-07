<?php
/**
 * The application's router
 *
 * This router "compiles" routes based on nikic's FastRoute project
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

use FastRoute\RouteCollector;

/**
 * The application's front controller
 *
 * @category   Examinr
 * @package    Router
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Router
{
    /**
     * @param \FastRoute\RouteCollector Collector for routes defined in the system
     */
    private $routeCollector;

    /**
     * @param callable Factory for the dispatcher
     */
    private $dispatcherFactory;

    /**
     * @param string Filename of the cache file
     */
    private $cacheFile;

    /**
     * @param bool Whether to invalidate and reload the cache
     */
    private $forceReload;

    /**
     * Creates instance
     *
     * @param \FastRoute\RouteCollector $routeCollector    Collector for routes defined in the system
     * @param callable                  $dispatcherFactory Factory for the dispatcher
     * @param string                    $cacheFile         Filename of the cache file
     * @param bool                      $forceReload       Whether to invalidate and reload the cache
     */
    public function __construct(
        RouteCollector $routeCollector,
        callable $dispatcherFactory,
        $cacheFile,
        $forceReload = false
    )
    {
        $this->routeCollector    = $routeCollector;
        $this->dispatcherFactory = $dispatcherFactory;
        $this->cacheFile         = $cacheFile;
        $this->forceReload       = $forceReload;
    }

    /**
     * Adds a route for a GET request
     *
     * @param string $path     The pattern of the path of the route
     * @param array  $callback The callback of the route
     *
     * @return \Examinr\Router\Router Return instance of itself to crate a fluent interface
     */
    public function get($path, array $callback)
    {
        return $this->addRoute('GET', $path, $callback);
    }

    /**
     * Adds a route for a POST request
     *
     * @param string $path     The pattern of the path of the route
     * @param array  $callback The callback of the route
     *
     * @return \Examinr\Router\Router Return instance of itself to crate a fluent interface
     */
    public function post($path, array $callback)
    {
        return $this->addRoute('POST', $path, $callback);
    }

    /**
     * Adds a route
     *
     * @param string $verb     The HTTP verb of the route
     * @param string $path     The pattern of the path of the route
     * @param array  $callback The callback of the route
     *
     * @return \Examinr\Router\Router Return instance of itself to crate a fluent interface
     */
    public function addRoute($verb, $path, array $callback)
    {
        $this->routeCollector->addRoute($verb, $path, $callback);

        return $this;
    }

    /**
     * Gets the dispatcher
     *
     * @return \FastRoute\Dispatcher The dispatcher
     */
    public function getDispatcher()
    {
        if ($this->forceReload || !file_exists($this->cacheFile)) {
            $dispatchData = $this->buildCache();
        } else {
            $dispatchData = require $this->cacheFile;
        }

        return call_user_func($this->dispatcherFactory, $dispatchData);
    }

    /**
     * Builds the routes cache file
     *
     * @return array The dispatch data
     */
    private function buildCache()
    {
        $dispatchData = $this->routeCollector->getData();

        file_put_contents($this->cacheFile, '<?php return ' . var_export($dispatchData, true) . ';');

        return $dispatchData;
    }
}
