<?php

namespace Ddr\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Table;

/**
 * @ORM\Entity
 * @ORM\Table(name="content", repository="Ddr\Entity\Repository\ContentRepository")
 */
class Content
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, name="content_type")
     */
    protected $contentType;

    /**
     * @ORM\Column(type="string", length=2048, nullable=true)
     */
    protected $url;

    /**
     * @ORM\Column(type="string", length=32)
     */
    protected $hash;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    protected $tags;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    protected $createdAt;

    protected $data;

    public function __construct($contentType, $data, \DateTime $createdAt = null)
    {
        $this->contentType = $contentType;
        $this->data = $data;
        $this->hash = md5(sprintf('%s%s', $this->contentType, $this->data));

        if (null === $createdAt) {
            $createdAt = new \DateTime();
        }

        $this->createdAt = $createdAt;
    }

    public function getId()
    {
        return $this->id;
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

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function getTags()
    {
        if (null === $this->tags) {
            return array();
        }

        return $this->tags;
    }

    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function toArray()
    {
        return [
            'content_type' => $this->contentType,
            'hash' => $this->hash,
            'url' => $this->url,
            'tags' => implode(', ', $this->getTags()),
        ];
    }
}
