<?php return array (
  0 => 
  array (
    '/not-found' => 
    array (
      'GET' => 
      array (
        0 => 'Examinr\\Presentation\\Controller\\Error',
        1 => 'notFound',
      ),
    ),
    '/method-not-allowed' => 
    array (
      'GET' => 
      array (
        0 => 'Examinr\\Presentation\\Controller\\Error',
        1 => 'notAllowed',
      ),
    ),
    '/' => 
    array (
      'GET' => 
      array (
        0 => 'Examinr\\Presentation\\Controller\\Auth',
        1 => 'login',
      ),
    ),
    '/login' => 
    array (
      'POST' => 
      array (
        0 => 'Examinr\\Presentation\\Controller\\Auth',
        1 => 'doLogIn',
      ),
    ),
    '/forgot-password' => 
    array (
      'GET' => 
      array (
        0 => 'Examinr\\Presentation\\Controller\\Auth',
        1 => 'forgotPassword',
      ),
      'POST' => 
      array (
        0 => 'Examinr\\Presentation\\Controller\\Auth',
        1 => 'doForgotPassword',
      ),
    ),
    '/forgot-password/sent' => 
    array (
      'GET' => 
      array (
        0 => 'Examinr\\Presentation\\Controller\\Auth',
        1 => 'forgotPasswordSent',
      ),
    ),
    '/reset-password/success' => 
    array (
      'GET' => 
      array (
        0 => 'Examinr\\Presentation\\Controller\\Auth',
        1 => 'resetPasswordSuccess',
      ),
    ),
  ),
  1 => 
  array (
    'GET' => 
    array (
      0 => 
      array (
        'regex' => '~^(?|/reset\\-password/([^/]+))$~',
        'routeMap' => 
        array (
          2 => 
          array (
            0 => 
            array (
              0 => 'Examinr\\Presentation\\Controller\\Auth',
              1 => 'resetPassword',
            ),
            1 => 
            array (
              'token' => 'token',
            ),
          ),
        ),
      ),
    ),
    'POST' => 
    array (
      0 => 
      array (
        'regex' => '~^(?|/reset\\-password/([^/]+))$~',
        'routeMap' => 
        array (
          2 => 
          array (
            0 => 
            array (
              0 => 'Examinr\\Presentation\\Controller\\Auth',
              1 => 'doResetPassword',
            ),
            1 => 
            array (
              'token' => 'token',
            ),
          ),
        ),
      ),
    ),
  ),
);