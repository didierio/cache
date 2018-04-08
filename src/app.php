<?php

use Ddr\Component\Cache\Cache;
use Ddr\Component\Imagine\ImageOptimizer;
use Ddr\Component\OAuth2\Storage\GuzzleStorage;
use Ddr\Component\Security\Core\Authentication\Provider\OAuth2AuthentificationProvider;
use Ddr\Component\Security\Core\User\OAuth2UserProvider;
use Ddr\Component\Security\EntryPoint\OAuth2EntryPoint;
use Ddr\Component\Security\Http\Firewall\OAuth2Listener;
use OAuth2\OAuth2;
use Silex\Application;
use Silex\Provider;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;

$app = new Application();

$app->register(new Provider\UrlGeneratorServiceProvider());
$app->register(new Provider\ServiceControllerServiceProvider());
$app->register(new Provider\TwigServiceProvider());

$app['cache'] = function ($app) {
    return new Cache($app['orm.ems']['default'], $app['cache.upload_dir']);
};

$app['security.authentication_listener.factory.oauth2'] = $app->protect(function ($name, $options) use ($app) {
    $app['oauth2.service'] = $app->share(function () use ($app) {
        return new OAuth2(new GuzzleStorage('https://connect.didier.io'));
    });

    $app['security.authentication_provider.'.$name.'.oauth2'] = $app->share(function () use ($app) {
        return new OAuth2UserProvider();
    });

    $app['security.authentication_listener.'.$name.'.oauth2'] = $app->share(function () use ($app) {
        return new OAuth2Listener($app['security'], $app['security.authentication_manager']);
    });

    $app['security.authentication_provider.'.$name.'.oauth2'] = $app->share(function () use ($app) {
        return new OAuth2AuthentificationProvider($app['oauth2.service'], $app['security.user_checker']);
    });

    return array(
        'security.authentication_provider.'.$name.'.oauth2',
        'security.authentication_listener.'.$name.'.oauth2',
        null,
        'pre_auth'
    );
});

$app->register(new Provider\SecurityServiceProvider(), array(
    'security.firewalls' => [
        'api' => array(
            'pattern' => '^/api/cache',
            'stateless' => true,
            'oauth2' => true,
        ),
    ]
));

$app['extension_guesser'] = function () {
    return ExtensionGuesser::getInstance();
};

$app['image_optimizer'] = function ($app) {
    return new ImageOptimizer(array(
       'jpegoptim' => array(
           'bin' => '/usr/bin/jpegoptim',
           'max' => 70,
       ),
        'thumbnail' => array(
            'width' => 200,
            'height' => 200,
        ),
    ));
};

return $app;
