<?php

use Ddr\Component\Cache\Cache;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig', array());
})
->bind('homepage');

$app->post('/api/cache', function (Request $request) use ($app) {
    $content = [];
    $content = $app['cache']->handleRequest($request, $content);

    $content['permalink_url'] = $app['url_generator']->generate('hash', [
        'hash' => $content['hash']
    ], true);

    return new JsonResponse($content);
})
->bind('cache');

$app->get('/api/get/{hash}', function (Request $request, $hash) use ($app) {
    if (null === $content = $app['cache']->find($hash)) {
        throw new NotFoundHttpException(sprintf('No content for #%s', $hash));
    }

    return new BinaryFileResponse($app['cache']->getFilePath($content['hash']));
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

    return new Response($app['twig']->resolveTemplate($templates)->render([
        'code' => $code,
        'message' => $e->getMessage(),
    ]), $code);
});
