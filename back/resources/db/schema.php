<?php

$schema = new \Doctrine\DBAL\Schema\Schema();

$tag = $schema->createTable('tag');
$tag->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
$tag->addColumn('name', 'string', array('length' => 100));

$content = $schema->createTable('content');
$content->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
$content->addColumn('content_type', 'string', array('length' => 255));
$content->addColumn('url', 'string', array('length' => 2048));
$content->addColumn('hash', 'string', array('length' => 32));
$content->setPrimaryKey(array('id'));

$contentTag = $schema->createTable('content_tag');
$contentTag->addColumn('tag_id', 'integer');
$contentTag->addColumn('content_id', 'integer');
$contentTag->addForeignKeyConstraint('tag', ['tag_id'], ['id']);
$contentTag->addForeignKeyConstraint('content', ['content_id'], ['id']);
$contentTag->addUniqueIndex(['tag_id', 'content_id']);

return $schema;
