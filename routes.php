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
    ->get('/not-found', ['Examinr\Presentation\Controller\Error', 'notFound'])
    ->get('/method-not-allowed', ['Examinr\Presentation\Controller\Error', 'notAllowed'])
;

if (!$user->isLoggedIn()) {
    $router
        ->get('/', ['Examinr\Presentation\Controller\Auth', 'login'])
        ->post('/login', ['Examinr\Presentation\Controller\Auth', 'doLogIn'])
        ->get('/forgot-password', ['Examinr\Presentation\Controller\Auth', 'forgotPassword'])
        ->post('/forgot-password', ['Examinr\Presentation\Controller\Auth', 'doForgotPassword'])
        ->get('/forgot-password/sent', ['Examinr\Presentation\Controller\Auth', 'forgotPasswordSent'])
        ->get('/reset-password/success', ['Examinr\Presentation\Controller\Auth', 'resetPasswordSuccess'])
        ->get('/reset-password/{token}', ['Examinr\Presentation\Controller\Auth', 'resetPassword'])
        ->post('/reset-password/{token}', ['Examinr\Presentation\Controller\Auth', 'doResetPassword'])
    ;
} else {
    $router
        ->post('/logout', ['Examinr\Presentation\Controller\Auth', 'doLogOut'])

        ->get('/', ['Examinr\Presentation\Controller\Index', 'index'])

        ->get('/settings/maintenance', ['Examinr\Presentation\Controller\Maintenance', 'overview'])

        ->get('/settings/users', ['Examinr\Presentation\Controller\User', 'overview'])
        ->get('/settings/users/add', ['Examinr\Presentation\Controller\User', 'add'])
        ->post('/settings/users/add', ['Examinr\Presentation\Controller\User', 'doAdd'])
        ->get('/settings/user-info', ['Examinr\Presentation\Controller\User', 'info'])
        ->post('/settings/user-info', ['Examinr\Presentation\Controller\User', 'doInfo'])
        ->get('/settings/auth-log', ['Examinr\Presentation\Controller\User', 'authLog'])

        ->get('/tasks/{commit}', ['Examinr\Presentation\Controller\Task', 'result'])

        ->get('/projects', ['Examinr\Presentation\Controller\Project', 'overview'])
    ;
}
