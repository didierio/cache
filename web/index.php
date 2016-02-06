<?php

ini_set('display_errors', 0);

require_once __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../src/app.php';

require __DIR__.'/../config/prod.php';
require __DIR__.'/../src/controllers.php';

use Symfony\Component\HttpFoundation\Request;

Request::setTrustedProxies(array('127.0.0.1'));

$app['http_cache']->run();
