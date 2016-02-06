<?php

use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\Command\SchemaTool as DoctrineCommand;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputOption;

$console = new Application('My Silex Application', 'n/a');
$console->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'));

$console->setHelperSet(new HelperSet([
    'db' => new ConnectionHelper($app["db"]),
    'em' => new EntityManagerHelper($app["orm.em"]),
]));

$console->addCommands([
    new DoctrineCommand\CreateCommand(),
    new DoctrineCommand\DropCommand(),
    new DoctrineCommand\UpdateCommand(),
]);
return $console;
