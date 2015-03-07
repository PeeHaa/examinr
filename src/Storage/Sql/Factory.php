<?php
/**
 * Factory for SQL storage adapters
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Storage
 * @subpackage Sql
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Storage\Sql;

use Symfony\Component\HttpFoundation\Request;

/**
 * Factory for SQL storage adapters
 *
 * @category   Examinr
 * @package    Storage
 * @subpackage Sql
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Factory
{
    /**
     * @var \PDO The database connection
     */
    private $dbConnection;

    /**
     * @var \Symfony\Component\HttpFoundation\Request The request object
     */
    private $request;

    /**
     * Creates instance
     *
     * @param \PDO                                      $dbConnection The database connection
     * @paran \Symfony\Component\HttpFoundation\Request $request      The request object
     */
    public function __construct(\PDO $dbConnection, Request $request)
    {
        $this->dbConnection = $dbConnection;
        $this->request      = $request;
    }

    /**
     * Builds SQL adapter
     *
     * @param string $name The name of the adapter to build
     *
     * @return \Examinr\Storage\Sql\Sql The SQL adapter
     */
    public function build($name)
    {
        return $this->buildWithDependencies($name);
    }

    /**
     * Builds the object with dependencies
     *
     * @param string $name The name of the class
     *
     * @return \Examinr\Storage\Sql\Sql The built object
     */
    private function buildWithDependencies($name)
    {
        $class = new \ReflectionClass($name);
        $constructor  = $class->getConstructor();
        $dependencies = $this->getDependencies($constructor->getParameters());

        return $class->newInstanceArgs($dependencies);
    }

    /**
     * Gets dependencies to build the object
     *
     * @param array $parameters The parameters of the constructor
     *
     * @return array The dependencies
     */
    private function getDependencies(array $parameters)
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependencies[] = $this->getDependency($parameter->getClass()->name);
        }

        return $dependencies;
    }

    /**
     * Gets a dependency
     *
     * @param string $name The name of the dependency
     *
     * @return mixed The dependency
     *
     * @throws \Examinr\Storage\Sql\UnknownDependencyException When trying to load an undefined dependency
     */
    private function getDependency($name)
    {
        switch ($name) {
            case 'PDO':
                return $this->dbConnection;

            case 'Symfony\Component\HttpFoundation\Request';
                return $this->request;

            default:
                throw new UnknownDependencyException('The dependency (`' . $name . '`) is not defined.');
        }
    }
}
