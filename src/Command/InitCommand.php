<?php

namespace Kapcus\DbChanger\Command;

use Kapcus\DbChanger\Model\Exception\OutOfSyncException;
use Kapcus\DbChanger\Model\IConfigurator;
use Kapcus\DbChanger\Model\ILoader;
use Kapcus\DbChanger\Model\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
	/**
	 * @var \Kapcus\DbChanger\Model\ILoader
	 */
	public $loader;

	/**
	 * @var \Kapcus\DbChanger\Model\Manager
	 */
	public $manager;

	/**
	 * @var \Kapcus\DbChanger\Model\IConfigurator
	 */
	public $configurator;

	public function __construct(ILoader $loader, Manager $manager, IConfigurator $configurator) {
		$this->loader = $loader;
		$this->manager = $manager;
		$this->configurator = $configurator;
		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName('dbchanger:init')
			->setDescription('Initialize environments defined in config file');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		try {
			$this->manager->initializeUsers($this->configurator->getUsers());
			$this->manager->initializeGroups($this->configurator->getGroups());
			$this->manager->initializeEnvironments($this->configurator->getEnvironments());
			$output->writeln('Environments successfully initialized.');
		} catch (OutOfSyncException $e) {
			$output->writeln($e->getMessage().' Consider running "reinit" command.');
		}
	}
}