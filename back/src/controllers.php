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

$app->post('/api/cache', function (Request $request) use ($app) {
    $content = $app['cache']->handleRequest($request);

    $content = $content->toArray();
    $content['permalink_url'] = $app['url_generator']->generate('hash', [
        'hash' => $content['hash'],
    ], true);

    return new JsonResponse($content);
})
->bind('cache');

$app->get('/api/get/{hash}', function (Request $request, $hash) use ($app) {
    if (null === $content = $app['cache']->find($hash)) {
        throw new NotFoundHttpException(sprintf('No content for #%s', $hash));
    }

    $path = $app['cache']->getFilePath($content->getHash());
    $createdAt = \DateTime::createFromFormat('U', filectime($path));

    return new BinaryFileResponse($path, 200, array(
        'Cache-Control' => 'public',
        'Last-Modified' => $createdAt,
        'Etag' => sprintf('content_%d_%d', $createdAt->getTimestamp(), $content->getId()),
    ));
})
->bind('hash');

$app->get('/api/photos/{hash}', function (Request $request, $hash) use ($app) {
    if (null === $content = $app['cache']->find($hash)) {
        throw new NotFoundHttpException(sprintf('No content for #%s', $hash));
    }

    $extensionGuesser = ExtensionGuesser::getInstance();
    $path = $app['cache']->getFilePath($content->getHash());
    $image = file_get_contents($path);

    if ($request->query->has('width') || $request->query->has('height')) {
        $image = $app['image_optimizer']->resize(
            $image,
            $extensionGuesser->guess($content->getContentType()),
            $request->query->get('width', null),
            $request->query->get('height', null)
        );
    }

    $createdAt = \DateTime::createFromFormat('U', filectime($path));

    return new Response($image, 200, array(
        'Accept-Ranges' => 'bytes',
        'Content-Type' => $content->getContentType(),
        'Content-Length' => strlen($image),
        'Cache-Control' => 'public',
        'Last-Modified' => $createdAt,
        'Etag' => sprintf('photo_%d_%d_%s_%s', $createdAt->getTimestamp(), $content->getId(), $request->query->get('width', 'x'), $request->query->get('height', 'x')),
    ));
})
->bind('photo');

$app->get('/api/photos/{hash}/thumbnail', function (Request $request, $hash) use ($app) {
    if (null === $content = $app['cache']->find($hash)) {
        throw new NotFoundHttpException(sprintf('No content for #%s', $hash));
    }

    $extensionGuesser = ExtensionGuesser::getInstance();
    $path = $app['cache']->getFilePath($content->getHash());
    $image = $app['image_optimizer']->thumbnail(
        file_get_contents($path),
        $extensionGuesser->guess($content->getContentType()),
        $request->query->get('width', null),
        $request->query->get('height', null)
    );

    $createdAt = \DateTime::createFromFormat('U', filectime($path));

    return new Response($image, 200, array(
        'Accept-Ranges' => 'bytes',
        'Content-Type' => $content->getContentType(),
        'Content-Length' => strlen($image),
        'Cache-Control' => 'public',
        'Last-Modified' => $createdAt,
        'Etag' => sprintf('photo_thumbnail_%d_%d_%s_%s', $createdAt->getTimestamp(), $content->getId(), $request->query->get('width', 'x'), $request->query->get('height', 'x')),
    ));
})
->bind('photo_thumbnail');

$app->get('/api/cache/{hash}/remove', function (Request $request, $hash) use ($app) {
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
