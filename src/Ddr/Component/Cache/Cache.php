<?php

namespace Ddr\Component\Cache;

use GuzzleHttp\Client;

class Cache
{
    protected $directory;

    public function __construct($directory)
    {
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

    public function get($name)
    {
        $filename = sprintf('%s/%.5s/%s', $this->directory, $name, $name);

        return file_get_contents($filename);
    }
}
