<?php

namespace Kapcus\DbChanger\Command;

use Kapcus\DbChanger\Model\Exception\ConfigurationException;
use Kapcus\DbChanger\Model\Exception\DbChangeException;
use Kapcus\DbChanger\Model\Exception\EnvironmentException;
use Kapcus\DbChanger\Model\Exception\InstallationException;
use Kapcus\DbChanger\Model\Executor;
use Kapcus\DbChanger\Model\IConfigurator;
use Kapcus\DbChanger\Model\IExecutor;
use Kapcus\DbChanger\Model\ILoader;
use Kapcus\DbChanger\Model\DibiStorage;
use Kapcus\DbChanger\Model\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
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
			->setName('dbchanger:install')
			->setDescription('Install registered DbChange on given environment.')
			->addArgument('env', InputArgument::REQUIRED, 'Target environment code')
			->addArgument('code', InputArgument::REQUIRED, 'DbChange code to be installed');
			//->addOption('skip', 's', InputOption::VALUE_OPTIONAL, 'If manual parts of db change should be skipped or not.', true);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$environmentCode = strtoupper($input->getArgument('env'));

		$dbChangeCode = strtoupper($input->getArgument('code'));

		try {
			$environment = $this->manager->getEnvironmentByCode($environmentCode);
			$dbChange = $this->manager->getDbChangeByCode($dbChangeCode);
			$output->writeln(sprintf('Installing DbChange %s into environment %s....', $dbChangeCode, $environment->getCode()));
			$this->manager->installDbChange($environment, $this->configurator->getEnvironmentConnectionConfigurations($environment->getCode()), $dbChange);
			exit(0);
		} catch (DbChangeException $e) {
			$output->writeln($e->getMessage());
		} catch (InstallationException $e) {
			$output->writeln($e->getMessage());
		} catch (EnvironmentException $e) {
			$output->writeln($e->getMessage());
		}
		$output->writeln(sprintf('Installing aborted. Check log to identify which query has failed, use \'status\' command.', $dbChangeCode));
		exit(1);
	}
}