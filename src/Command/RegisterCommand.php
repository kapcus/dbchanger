<?php

namespace Kapcus\DbChanger\Command;

use Kapcus\DbChanger\Model\Exception\DbChangeException;
use Kapcus\DbChanger\Model\Exception\EnvironmentException;
use Kapcus\DbChanger\Model\IConfigurator;
use Kapcus\DbChanger\Model\IGenerator;
use Kapcus\DbChanger\Model\ILoader;
use Kapcus\DbChanger\Model\DibiStorage;
use Kapcus\DbChanger\Model\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RegisterCommand extends Command
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
			->setName('dbchanger:register')
			->setDescription('Register DbChange and prepare it for further installation')
			->addArgument('code', InputArgument::REQUIRED, 'DbChange code to be registered.')
			->addOption('debug', 'd', InputOption::VALUE_NONE, 'y')
			->addOption('overwrite', 'o', InputOption::VALUE_NONE, 'y');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$dbChangeCode = strtoupper($input->getArgument('code'));

		try {
			$dbChange = $this->loader->loadDbChangeFromInputDirectory($this->manager->getGroups(), $dbChangeCode);
			$this->manager->registerDbChange($dbChange, $input->getOption('debug'), $input->getOption('overwrite'));
			$output->writeln(sprintf('DbChange %s successfully registered.', $dbChangeCode));
		} catch (DbChangeException $e) {
			$output->writeln($e->getMessage());
		}
	}
}