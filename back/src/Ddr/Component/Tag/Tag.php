<?php

namespace Ddr\Component\Tag;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;

class Tag
{
    protected $connection;

    public function __construct(Connection $connection, $directory)
    {
        $this->connection = $connection;
    }

    public function handleRequest(Request $request, $content)
    {
        if (false === $request->request->has('tags')) {
            return $content;
        }

        $tagNames = explode(', ', $request->request->get('tags'));
        $tags = [];

        foreach ($tagNames as $tagName) {
            $slug = $this->sluggify($tagName);
            $sql = "SELECT * FROM tag WHERE slug = ?";
            $tag = $this->connection->fetchAssoc($sql, array($slug));

            if (null === $content) {
                $tag = ['name' => $tagName, 'slug' => $slug];
                $tag = $this->save($tag);
            }

            $tags[] = $tag;
        }

        foreach ($tags as $tag) {
            $sql = "SELECT * FROM content_tag WHERE content = ? AND tag = ?";
            $tag = $this->connection->fetchAssoc($sql, array($content['id'], $tag['id']));            
        }

        return array_merge($content, $this->save($requestFile));
    }

    public function save(array $tag)
    {
        $tag['id'] = $this->connection->insert('tag', $tag);

        return $tag;
    }

    protected function sluggify($text)
    {
        # Prep string with some basic normalization
        $text = strtolower($text);
        $text = strip_tags($text);
        $text = stripslashes($text);
        $text = html_entity_decode($text);

        # Remove quotes (can't, etc.)
        $text = str_replace('\'', '', $text);

        # Replace non-alpha numeric with hyphens
        $match = '/[^a-z0-9]+/';
        $replace = '-';
        $text = preg_replace($match, $replace, $text);

        $text = trim($text, '-');

        return $text;
    }
}
