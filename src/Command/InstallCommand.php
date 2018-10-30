<?php

namespace Kapcus\DbChanger\Command;

use Kapcus\DbChanger\Model\Exception\DbChangeException;
use Kapcus\DbChanger\Model\Exception\EnvironmentException;
use Kapcus\DbChanger\Model\Exception\InstallationException;
use Kapcus\DbChanger\Model\IConfigurator;
use Kapcus\DbChanger\Model\Manager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends FormattedOutputCommand
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
			->setName('dbchanger:install')
			->setDescription('Install registered DbChange on given environment.')
			->addArgument('env', InputArgument::REQUIRED, 'Target environment code')
			->addArgument('code', InputArgument::REQUIRED, 'DbChange code to be installed')
			->addOption('force', 'f', InputOption::VALUE_NONE, 'Ignores when required DbChange is outdated')
			->addOption('stop', 's', InputOption::VALUE_NONE, 'Stops immediately before first fragment');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$environmentCode = strtoupper($input->getArgument('env'));

		$dbChangeCode = strtoupper($input->getArgument('code'));

		try {
			$environment = $this->manager->getEnvironmentByCode($environmentCode);
			$dbChange = $this->manager->getActiveDbChangeByCode($dbChangeCode);
			$this->manager->installDbChange($this->configurator->getGroups(), $environment, $this->configurator->getEnvironmentConnectionConfigurations($environment->getCode()), $dbChange, $input->getOption('force'), $input->getOption('stop'));
			$output->writeln('OK - DbChange installed successfully.');
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