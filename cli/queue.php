<?php
/**
 * Queue runner
 *
 * This script will start a new queue runner which executes tasks as they come in.
 * The execution of specific tasks will be delegated to individual workers so that the queue
 * can continue running in parallel
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Cli
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    1.0.0
 */
namespace Examinr\Cli;

use Examinr\Queue\Worker\Factory;
use Examinr\Queue\Queue;

require_once __DIR__ . '/../bootstrap.php';

$workerFactory = new Factory($dbConnection, __DIR__);

$queue = new Queue($dbConnection, $workerFactory, __DIR__ . '/../data/queue.pid', 5);

$queue->start();
