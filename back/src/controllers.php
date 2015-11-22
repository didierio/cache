<?php

use Ddr\Component\Cache\Cache;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
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

$app->post('/cache', function (Request $request) use ($app) {
    $content = $app['cache']->handleRequest($request);

    $content = $content->toArray();
    $content['permalink_url'] = $app['url_generator']->generate('hash', [
        'hash' => $content['hash'],
    ], true);

    return new JsonResponse($content);
})
->bind('cache');

$app->get('/{hash}', function (Request $request, $hash) use ($app) {
    if (null === $content = $app['cache']->find($hash)) {
        throw new NotFoundHttpException(sprintf('No content for #%s', $hash));
    }

    $content = $content->toArray();

    return new BinaryFileResponse($app['cache']->getFilePath($content['hash']));
})
->bind('hash');


$app->get('/search', function (Request $request) use ($app) {
    if (null === $content = $app['cache']->all()) {
        throw new NotFoundHttpException(sprintf('No content for #%s', $hash));
    }

    $extensionGuesser = ExtensionGuesser::getInstance();
    $content = $content->toArray();
    $image = file_get_contents($app['cache']->getFilePath($content['hash']));

    if ($request->query->has('width') || $request->query->has('height')) {
        $image = $app['image_optimizer']->resize(
            $image,
            $extensionGuesser->guess($content['content_type']),
            $request->query->get('width', null),
            $request->query->get('height', null)
        );
    }

    return new Response($image, 200, array(
        'Accept-Ranges' => 'bytes',
        'Cache-Control' => 'public',
        'Content-Type' => $content['content_type'],
        'Content-Length' => strlen($image),
    ));
})
->bind('search');

$app->get('/{hash}/picture', function (Request $request, $hash) use ($app) {
    if (null === $content = $app['cache']->find($hash)) {
        throw new NotFoundHttpException(sprintf('No content for #%s', $hash));
    }

    $content = $content->toArray();
    $image = file_get_contents($app['cache']->getFilePath($content['hash']));

    if ($request->query->has('width') || $request->query->has('height')) {
        $extensionGuesser = ExtensionGuesser::getInstance();
        $image = $app['image_optimizer']->resize(
            $image,
            $extensionGuesser->guess($content['content_type']),
            $request->query->get('width', null),
            $request->query->get('height', null)
        );
    }

    return new Response($image, 200, array(
        'Accept-Ranges' => 'bytes',
        'Cache-Control' => 'public',
        'Content-Type' => $content['content_type'],
        'Content-Length' => strlen($image),
    ));
})
->bind('picture');

$app->get('/{hash}/thumbnail', function (Request $request, $hash) use ($app) {
    if (null === $content = $app['cache']->find($hash)) {
        throw new NotFoundHttpException(sprintf('No content for #%s', $hash));
    }

    $extensionGuesser = ExtensionGuesser::getInstance();
    $content = $content->toArray();
    $image = $app['image_optimizer']->thumbnail(
        file_get_contents($app['cache']->getFilePath($content['hash'])),
        $extensionGuesser->guess($content['content_type']),
        $request->query->get('width', null),
        $request->query->get('height', null)
    );

    return new Response($image, 200, array(
        'Accept-Ranges' => 'bytes',
        'Cache-Control' => 'public',
        'Content-Type' => $content['content_type'],
        'Content-Length' => strlen($image),
    ));
})
->bind('photo_thumbnail');

$app->get('/{hash}/remove', function (Request $request, $hash) use ($app) {
    if (null === $content = $app['cache']->find($hash)) {
        throw new NotFoundHttpException(sprintf('No content for #%s', $hash));
    }

    $app['cache']->remove($content);

    return new JsonResponse();
})
->bind('remove');

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        throw $e;
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
