<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

require_once __DIR__ . '/model/DebugEnabler.php';
App\Model\DebugEnabler::setWorkDir(__DIR__ . '/../temp');

if (App\Model\DebugEnabler::isDebug()) {
    $configurator->setDebugMode(true);
} else {
    $configurator->setDebugMode([]); // Automatic detect by Nette
}

$configurator->enableTracy(__DIR__ . '/../log', 'pan@jakubboucek.cz');

$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

return $container;
