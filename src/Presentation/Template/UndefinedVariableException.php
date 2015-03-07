<?php
/**
 * Exception which gets thrown when trying to access an undefined template variable
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Presentation
 * @subpackage Template
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Presentation\Template;

/**
 * Exception which gets thrown when trying to access an undefined template variable
 *
 * @category   Examinr
 * @package    Presentation
 * @subpackage Template
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class UndefinedVariableException extends \Exception
{
}
