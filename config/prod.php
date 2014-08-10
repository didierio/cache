<?php

// configure your app for the production environment

$app['twig.path'] = array(__DIR__.'/../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');
$app['cache.upload_dir'] = '/var/uploads';

$app['db.options'] = [
    'driver'    => 'pdo_pgsql',
    'host'      => 'localhost',
    'dbname'    => 'media',
    'user'      => 'media',
    'password'  => 'media',
    'charset'   => 'utf8',
];
