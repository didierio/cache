<?php

namespace Ddr\Component\Cache;

class RequestFile
{
    protected $url;
    protected $contentType;
    protected $data;
    protected $tags;

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function getHash()
    {
        return md5($this->getContentType().$this->getData());
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    public function toArray()
    {
        return [
            'content_type' => $this->getContentType(),
            'hash' => $this->getHash(),
            'url' => $this->getUrl(),
            'tags' => $this->getTags(),
        ];
    }
}
