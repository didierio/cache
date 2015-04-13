<?php

namespace Ddr\Component\Cache;

use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Cache
{
    protected $connection;
    protected $directory;

    public function __construct(Connection $connection, $directory)
    {
        $this->connection = $connection;
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
        $requestFile = new RequestFile();

        if ($request->request->has('url')) {
            $url = $request->request->get('url');
            $client = new Client();
            $response = $client->get($url);

            $requestFile
                ->setUrl($response->getEffectiveUrl())
                ->setContentType($response->getHeader('content-type'))
                ->setData($response->getBody())
            ;

            return $this->save($requestFile);
        }

        $content = trim($request->getContent());

        if (null !== $content && '' !== $content) {
            $finfo = finfo_open();
            $mimeType = finfo_buffer($finfo, $content, FILEINFO_MIME_TYPE);
            finfo_close($finfo);

            $requestFile
                ->setUrl(md5($content))
                ->setContentType($mimeType)
                ->setData($content)
            ;

            return $this->save($requestFile);
        }

        throw new BadRequestHttpException('No file found in request');
    }

    public function save(RequestFile $requestFile)
    {
        $sql = "SELECT * FROM content WHERE url = ?";
        $content = $this->connection->fetchAssoc($sql, array($requestFile->getUrl('url')));

        if (false === $content) {
            $cache->set($requestFile->getHash(), $requestFile->getData());

            $content = $requestFile->toArray();
            $content['id'] = $this->connection->insert('content', $content);
        }

        return $content;

    }
}
