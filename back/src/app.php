<?php

use Ddr\Component\OAuth2\Storage\GuzzleStorage;
use Ddr\Component\Security\Core\User\OAuth2UserProvider;
use Ddr\Component\Security\EntryPoint\OAuth2EntryPoint;
use Ddr\Component\Security\Http\Firewall\OAuth2Listener;
use OAuth2\OAuth2;
use Silex\Application;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;

$app = new Application();
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider());

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    // add custom globals, filters, tags, ...

    return $twig;
}));

$app['security.authentication_listener.factory.oauth2'] = $app->protect(function ($name, $options) use ($app) {
    $app['oauth2.service'] = $app->share(function () use ($app) {
        return new OAuth2(new GuzzleStorage('http://connect.didier.io'));
    });

    // define the authentication provider object
    $app['security.authentication_provider.'.$name.'.oauth2'] = $app->share(function () use ($app) {
        return new OAuth2UserProvider($app['oauth2.service'], $app['security.user_checker']);
    });

    // define the authentication listener object
    $app['security.authentication_listener.'.$name.'.oauth2'] = $app->share(function () use ($app) {
        return new OAuth2Listener($app['security'], $app['security.authentication_manager']);
    });

    return array(
        // the authentication provider id
        'security.authentication_provider.'.$name.'.oauth2',
        // the authentication listener id
        'security.authentication_listener.'.$name.'.oauth2',
        // the entry point id
        null,
        // the position of the listener in the stack
        'pre_auth'
    );
});


$app->register(new SecurityServiceProvider(), array(
    'security.firewalls' => [
        'api' => array(
            'pattern' => '^/api/cache',
            'stateless' => true,
            'oauth2' => true,
        ),
    ]
));

return $app;
