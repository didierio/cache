<?php

use Ddr\Component\Cache\Cache;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => $app['db.options'],
));

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig', array());
})
->bind('homepage');

$app->post('/api/cache', function (Request $request) use ($app) {
    $url = $request->request->get('url');

    if (null === $url) {
        throw new BadRequestHttpException('Missing url');
    }

    $sql = "SELECT * FROM content WHERE url = ?";
    $content = $app['db']->fetchAssoc($sql, array($url));

    if (false === $content) {
        $client = new Client();
        $response = $client->get($url);

        $content = [
            'url' => $response->getEffectiveUrl(),
            'content_type' => $response->getHeader('content-type'),
            'data' => $response->getBody(),
        ];

        $content['hash'] = md5($content['content_type'].$content['data']->__toString());

        $cache = new Cache(__DIR__.sprintf('/../%s', $app['cache.upload_dir']));
        $cache->set($content['hash'], $content['data']);

        unset($content['data']);
        $content['id'] = $app['db']->insert('content', $content);
    }

    $content['permalink_url'] = $app['url_generator']->generate('hash', [
        'hash' => $content['hash']
    ], true);

    return new JsonResponse($content);
})
->bind('cache');

$app->get('/api/cache/{hash}', function (Request $request, $hash) use ($app) {
    $sql = "SELECT * FROM content WHERE hash = ?";
    $content = $app['db']->fetchAssoc($sql, array($hash));

    if (false === $content) {
        throw new NotFoundHttpException(sprintf('No content for #%s', $hash));
    }

    $cache = new Cache(__DIR__.sprintf('/../.%s', $app['cache.upload_dir']));
    $data = $cache->get($content['hash']);

    return new Response($data, 200, [
        'content-type' => $content['content_type'],
    ]);
})
->bind('hash');

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
