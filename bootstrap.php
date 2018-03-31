<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$loader = new Nette\DI\ContainerLoader(__DIR__ . '/temp', IS_DEBUG_MODE);
$class = $loader->load(function($compiler) {
	$compiler->loadConfig(__DIR__ . '/config/config.neon');
	$compiler->loadConfig(__DIR__ . '/config.local.neon');
});
$container = new $class;

$application = new Application('DbChanger', '0.3.0');
$application->add($container->getByType(Kapcus\DbChanger\Command\CheckCommand::class));
$application->add($container->getByType(Kapcus\DbChanger\Command\MarkCommand::class));
$application->add($container->getByType(Kapcus\DbChanger\Command\InitCommand::class));
$application->add($container->getByType(Kapcus\DbChanger\Command\ReinitCommand::class));
$application->add($container->getByType(Kapcus\DbChanger\Command\InstallCommand::class));
$application->add($container->getByType(Kapcus\DbChanger\Command\GenerateCommand::class));
$application->add($container->getByType(Kapcus\DbChanger\Command\RegisterCommand::class));
$application->add($container->getByType(Kapcus\DbChanger\Command\StatusCommand::class));
exit($application->run());

