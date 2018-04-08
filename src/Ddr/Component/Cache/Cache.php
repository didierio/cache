<?php

namespace Ddr\Component\Cache;

use Ddr\Entity\Content;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Cache
{
    protected $objectManager;
    protected $directory;

    public function __construct(ObjectManager $objectManager, $directory)
    {
        $this->objectManager = $objectManager;
        $this->directory = $directory;
    }

    public function set($name, $resource)
    {
        $directory = sprintf('%s/%.5s', $this->directory, $name);

        if (false === is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = sprintf('%s/%s', $directory, $name);
        file_put_contents($filename, $resource);

        return $resource;
    }

    public function getFilePath($name)
    {
        return sprintf('%s/%.5s/%s', $this->directory, $name, $name);
    }

    public function handleRequest(Request $request)
    {
        if ($request->request->has('url')) {
            $url = $request->request->get('url');
            $client = new Client();
            $response = $client->get($url);
            $content = new Content($response->getHeader('content-type'), $response->getBody());

            $content
                ->setUrl($response->getEffectiveUrl())
                ->setTags(explode(', ', $request->request->get('tags')))
            ;

            return $this->save($content);
        }

        $contentData = $request->getContent();

        if (null !== $contentData && '' !== $contentData) {
            $finfo = finfo_open();
            $mimeType = finfo_buffer($finfo, $contentData, FILEINFO_MIME_TYPE);
            finfo_close($finfo);

            if ('application/octet-stream' === $mimeType) {
                $mimeType = $request->headers->get('content-type', $mimeType);
            }

            $content = new Content($mimeType, $contentData);
            $content
                ->setTags(explode(', ', $request->request->get('tags')))
            ;

            return $this->save($content);
        }

        throw new BadRequestHttpException('No file found in request');
    }

    public function save(Content $content)
    {
        if (false === $this->exists($content->getHash())) {
            $this->set($content->getHash(), $content->getData());

            $this->objectManager->persist($content);
            $this->objectManager->flush($content);
        }

        return $content;
    }

    public function createResponse(Content $content)
    {
        $path = $this->getFilePath($content->getHash());
        $createdAt = \DateTime::createFromFormat('U', filectime($path));

        return new BinaryFileResponse($path, 200, array(
            'Cache-Control' => 'public',
            'Last-Modified' => $createdAt,
            'Content-Type' => $content->getContentType(),
            'Etag' => sprintf('content_%d_%d', $createdAt->getTimestamp(), $content->getId()),
        ));
    }

    public function exists($hash)
    {
        return null !== $this->objectManager->getRepository('Ddr\Entity\Content')->findOneBy(array('hash' => $hash));
    }

    public function find($hash)
    {
        return $this->objectManager->getRepository('Ddr\Entity\Content')->findOneBy(array('hash' => $hash));
    }

    public function remove(Content $content)
    {
        $this->objectManager->remove($content);
        $this->objectManager->flush($content);
    }
}
