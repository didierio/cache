<?php

use Silex\Provider;

$app->register(new Provider\HttpFragmentServiceProvider());
$app->register(new Provider\ServiceControllerServiceProvider());
$app->register(new Provider\TwigServiceProvider());

$app['twig.path'] = array(__DIR__.'/../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');
$app['cache.upload_dir'] = __DIR__.'/../var/uploads';

$app['db.options'] = [
    'driver'    => 'pdo_pgsql',
    'host'      => 'database',
    'port'      => 5432,
    'dbname'    => 'didierio_media',
    'user'      => 'didierio',
    'password'  => 'didierio',
    'charset'   => 'utf8',
];

$app->register(new Provider\DoctrineServiceProvider(), [
    'db.options' => $app['db.options'],
]);

$app->register(new Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider, [
        "orm.em.options" => [
            "mappings" => [
                [
                    "type"                         => "annotation",
                    "namespace"                    => "Ddr\Entity",
                    "path"                         => realpath(__DIR__."/../src/Ddr/Entity"),
                    "use_simple_annotation_reader" => false,
                ],
            ],
        ],
    ]
);
