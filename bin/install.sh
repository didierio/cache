#!/bin/bash
if [ -z "$1" ]
then
  env="dev"
else
  env=$1
fi

echo "Environment: " $env
echo ""

echo ">>> Cleanup cache"
rm -rf var/{cache,logs}/*

if [ ! -f composer.phar ]; then
    echo ">>> Downloading composer.phar"
    curl -s http://getcomposer.org/installer | php
fi

if [ ! -d vendor ]; then
    echo ">>> Installing dependencies"
    php composer.phar install
fi

echo ">>> Dropping database"
php bin/console orm:schema-tool:drop --force
echo ">>> Creating database"
php bin/console orm:schema-tool:create
