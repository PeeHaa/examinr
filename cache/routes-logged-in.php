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
  ),
  1 => 
  array (
  ),
);