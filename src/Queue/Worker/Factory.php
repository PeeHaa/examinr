<?php
/**
 * Worker factory
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Queue
 * @subpackage Worker
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Queue\Worker;

/**
 * Worker factory
 *
 * @category   Examinr
 * @package    Queue
 * @subpackage Worker
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Factory
{
    /**
     * @var \PDO The database connection
     */
    private $dbConnection;

    /**
     * @var string The location of the cli scripts
     */
    private $cliPath;

    /**
     * Creates instances
     *
     * @param \PDO   $dbConnection The database connection
     * @param string $cliPath      The path to the CLI scipts
     */
    public function __construct(\PDO $dbConnection, $cliPath)
    {
        $this->dbConnection = $dbConnection;
        $this->cliPath      = $cliPath;
    }

    /**
     * Builds the worker
     *
     * @param string $type The type of worker to build
     *
     * @return \Examinr\Queue\Worker\Runnable The worker
     */
    public function build($type)
    {
        switch ($type) {
            default:
                throw new \Exception('No workers has been defined');
        }
    }
}
