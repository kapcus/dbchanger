<?php

namespace Kapcus\DbChanger\Command;

use Kapcus\DbChanger\Model\Exception\OutOfSyncException;
use Kapcus\DbChanger\Model\IConfigurator;
use Kapcus\DbChanger\Model\Manager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends FormattedOutputCommand
{
	/**
	 * @var \Kapcus\DbChanger\Model\Manager
	 */
	public $manager;

	/**
	 * @var \Kapcus\DbChanger\Model\IConfigurator
	 */
	public $configurator;

	public function __construct(Manager $manager, IConfigurator $configurator) {
		$this->manager = $manager;
		$this->configurator = $configurator;
		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName('dbchanger:init')
			->setDescription('Initialize environments defined in config file.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		try {
			$this->manager->initializeGroups($this->configurator->getGroups());
			$this->manager->initializeEnvironments($this->configurator->getEnvironments());
			$output->writeln('Environments successfully initialized.');
		} catch (OutOfSyncException $e) {
			$output->writeln($e->getMessage().' Consider running "reinit" command.');
		}
	}
}