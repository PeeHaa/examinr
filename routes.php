<?php
/**
 * Routes definitions of the project
 *
 * This file contains all the routes of the project
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr;

$router
    ->get('/', ['Examinr\Presentation\Controller\Index', 'index'])
    ->get('/not-found', ['Examinr\Presentation\Controller\Error', 'notFound'])
    ->get('/method-not-allowed', ['Examinr\Presentation\Controller\Error', 'notAllowed'])
;
