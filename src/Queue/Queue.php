<?php
/**
 * Queue handler
 *
 * This class represent a work queue. It will keep polling the database for work to do
 * and when needed spawns a new worker process to run specific tasks
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Queue
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Queue;

use Examinr\Queue\Worker\Factory;

/**
 * Queue handler
 *
 * @category   Examinr
 * @package    Queue
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Queue
{
    /**
     * @var \PDO The database connection
     */
    private $dbConnection;

    /**
     * @var string The location of the pid file
     */
    private $pidFile;

    /**
     * @var int The second to sleep between checking the database for new tasks
     */
    private $interval;

    /**
     * Creates instance
     *
     * @param \PDO                         $dbConnection The database connection
     * param \Examinr\Queue\Worker\Factory $factory      The worker factory
     * @param string                       $pidFile      The pid file location
     * @param int                          $interval     The interval between checking the database for new tasks
     */
    public function __construct($dbConnection, Factory $factory, $pidFile, $interval = 5)
    {
        $this->dbConnection = $dbConnection;
        $this->factory      = $factory;
        $this->pidFile      = $pidFile;
        $this->interval     = $interval;
    }

    /**
     * Starts the queue
     *
     * When starting the queue a pid file is created and locked to prevent spawning multiple queue scripts
     */
    public function start()
    {
        $pid = fopen($this->pidFile, 'w');

        if (!flock($pid, LOCK_EX|LOCK_NB, $result)) {
            return;
        }

        $this->run();
    }

    /**
     * Runs the queue
     */
    private function run()
    {
        $query = 'SELECT id, type';
        $query.= ' FROM queue';
        $query.= ' WHERE timestamp < :timestamp';

        $stmt = $this->dbConnection->prepare($query);

        while (true) {
            $datetime = new \DateTime();

            $stmt->execute([
                'timestamp' => $datetime->format('Y-m-d H:i:s'),
            ]);

            foreach ($stmt->fetchAll() as $task) {
                $worker = $this->factory->build($task['type']);

                $worker->run($task['id']);
            }

            sleep($this->interval);
        }
    }
}
