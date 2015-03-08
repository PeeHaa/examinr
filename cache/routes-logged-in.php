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
    '/logout' => 
    array (
      'POST' => 
      array (
        0 => 'Examinr\\Presentation\\Controller\\Auth',
        1 => 'doLogOut',
      ),
    ),
    '/' => 
    array (
      'GET' => 
      array (
        0 => 'Examinr\\Presentation\\Controller\\Index',
        1 => 'index',
      ),
    ),
    '/settings/users' => 
    array (
      'GET' => 
      array (
        0 => 'Examinr\\Presentation\\Controller\\User',
        1 => 'overview',
      ),
    ),
    '/settings/user-info' => 
    array (
      'GET' => 
      array (
        0 => 'Examinr\\Presentation\\Controller\\User',
        1 => 'info',
      ),
      'POST' => 
      array (
        0 => 'Examinr\\Presentation\\Controller\\User',
        1 => 'doInfo',
      ),
    ),
  ),
  1 => 
  array (
    'GET' => 
    array (
      0 => 
      array (
        'regex' => '~^(?|/tasks/([^/]+))$~',
        'routeMap' => 
        array (
          2 => 
          array (
            0 => 
            array (
              0 => 'Examinr\\Presentation\\Controller\\Task',
              1 => 'result',
            ),
            1 => 
            array (
              'commit' => 'commit',
            ),
          ),
        ),
      ),
    ),
  ),
);