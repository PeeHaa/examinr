<?php
/**
 * Interface for workers
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
 * Interface for workers
 *
 * @category   Examinr
 * @package    Queue
 * @subpackage Worker
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
interface Runnable
{
    /**
     * Runs the worker
     *
     * @param int $id The id of the queued task to run
     */
    public function run($id);
}
