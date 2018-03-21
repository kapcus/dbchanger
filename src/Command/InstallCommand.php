<?php

namespace Kapcus\DbChanger\Command;

use Kapcus\DbChanger\Model\Exception\DbChangeException;
use Kapcus\DbChanger\Model\Exception\EnvironmentException;
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
	public $environmentDescriptor;

	public function __construct(ILoader $loader, Manager $manager, IConfigurator $configurator) {
		$this->loader = $loader;
		$this->manager = $manager;
		$this->environmentDescriptor = $configurator;
		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName('dbchanger:install')
			->setDescription('Install registered DbChange on given environment')
			->addArgument('env', InputArgument::REQUIRED, 'Target environment code')
			->addArgument('code', InputArgument::REQUIRED, 'DbChange code to be installed')
			->addOption('skip', 's', InputOption::VALUE_OPTIONAL, 'If manual parts of db change should be skipped or not.', true);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$environmentCode = strtoupper($input->getArgument('env'));

		if (($environment = $this->environmentDescriptor->getEnvironmentByCode($environmentCode)) === null) {
			throw new EnvironmentException(sprintf('Unknown environment code %1$s, ensure this environment is defined in your configuration.', $environmentCode));
		}

		$dbChangeCode = strtoupper($input->getArgument('code'));
		$skipManual = (bool) $input->getOption('skip');

		try {
			if ($skipManual) {
				$output->writeln(sprintf('Manual installation steps will be generated into output folder but not installed.'));
			} else {
				$output->writeln(sprintf('Manual installation steps will be also installed.'));
			}
			$output->writeln(sprintf('Installing DbChange %s into environment %s....', $dbChangeCode, $environment->getCode()));
			$this->manager->installDbChange($environment, $dbChangeCode, $skipManual);
			if ($skipManual) {
				$output->writeln(sprintf('DbChange %s successfully installed to %s environment and manual steps were generated into output folder.', $dbChangeCode, $environment->getCode()));
			} else {
				$output->writeln(sprintf('DbChange %s successfully installed to %s environment (incl. manual steps).', $dbChangeCode, $environment->getCode()));
			}
		} catch (DbChangeException $e) {
			$output->writeln($e->getMessage());
		} catch (EnvironmentException $e) {
			$output->writeln($e->getMessage());
		}
	}
}