<?php
/**
 * This file simply links to the correct environment settings file
 *
 * When switching from one environment to another (e.g. from dev to production) the settings file needs to be changed
 * for things as enabling / disabling error reporting etc.
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @author     Pieter Hordijk <pieter@mindwarp.nl>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr;

require_once __DIR__ . '/init.example.php';
