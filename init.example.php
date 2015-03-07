<?php
/**
 * Environment specific settings
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @author     Pieter Hordijk <pieter@mindwarp.nl>
 * @copyright  Copyright (c) 2015 Mindwarp Rotterdam <http://mindwarp.nl>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr;

/**
 * Setup error reporting
 */
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

/**
 * Setup the timezone
 */
date_default_timezone_set('Europe/Amsterdam');

/**
 * Setup database connection
 */
$dbConnection = new \PDO('mysql:dbname=examinr;host=127.0.0.1;charset=utf8', 'root', '');
$dbConnection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
$dbConnection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$dbConnection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
