<?php
/**
 * Exception which gets thrown when trying to load an undefined dependency
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

/**
 * Exception which gets thrown when trying to load an undefined dependency
 *
 * @category   Examinr
 * @package    Storage
 * @subpackage Sql
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class UnknownDependencyException extends \Exception
{
}
