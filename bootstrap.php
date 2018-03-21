<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

/*$configurator = new Nette\Configurator;


$configurator->setDebugMode(true);
$configurator->enableDebugger(__DIR__ . '/../log');

$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();*/

$loader = new Nette\DI\ContainerLoader(__DIR__ . '/temp');
$class = $loader->load(function($compiler) {
	$compiler->loadConfig(__DIR__ . '/config.local.neon');
});
$container = new $class;

/*$conn = [
	'driver' => 'oci8',
	'host' => 'localhost',
	'user' => '',
	'password' => '',
	'servicename' => '',
	'dbname' => '',
	'port' => '1522',
];*/

/*$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src"), $isDevMode, null, null, false);
$entityManager = EntityManager::create($conn, $config);*/

$application = new Application('DbChanger', '0.2.0');
$application->add($container->getByType(Kapcus\DbChanger\Command\InitCommand::class));
$application->add($container->getByType(Kapcus\DbChanger\Command\ReinitCommand::class));
//$application->add($container->getByType(Kapcus\DbChanger\Command\InstallCommand::class));
$application->add($container->getByType(Kapcus\DbChanger\Command\GenerateCommand::class));
//$application->add($container->getByType(Kapcus\DbChanger\Command\RegisterCommand::class));
$application->run();

/*// Get application from DI container.
$application = $container->getByType(Contributte\Console\Application::class);

// Run application.
exit($application->run());*/
