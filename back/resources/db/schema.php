<?php

$schema = new \Doctrine\DBAL\Schema\Schema();

$post = $schema->createTable('content');
$post->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
$post->addColumn('content_type', 'string', array('length' => 255));
$post->addColumn('url', 'string', array('length' => 2048));
$post->addColumn('hash', 'string', array('length' => 32));
$post->setPrimaryKey(array('id'));

return $schema;
