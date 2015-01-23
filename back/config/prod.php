<?php

// configure your app for the production environment

$app['twig.path'] = array(__DIR__.'/../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');
$app['cache.upload_dir'] = '/var/uploads';

$app['db.options'] = [
    'driver'    => 'pdo_pgsql',
    'host'      => 'database',
    'port'      => 5432,
    'dbname'    => 'didierio_media',
    'user'      => 'didierio',
    'password'  => 'didierio',
    'charset'   => 'utf8',
];
