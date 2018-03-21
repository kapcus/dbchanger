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

class StatusCommand extends Command
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
			->setName('dbchanger:status')
			->setDescription('Checks whether given DbChange is installed on particular environment.')
			->addArgument('env', InputArgument::REQUIRED, 'Target environment code')
			->addArgument('code', InputArgument::REQUIRED, 'DbChange code to be checked');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$environmentCode = strtoupper($input->getArgument('env'));

		if (($environment = $this->environmentDescriptor->getEnvironmentByCode($environmentCode)) === null) {
			throw new EnvironmentException(sprintf('Unknown environment code %1$s, ensure this environment is defined in your configuration.', $environmentCode));
		}

		$dbChangeCode = strtoupper($input->getArgument('code'));

		try {
			$installationResults = $this->manager->checkDbChange($environment, $dbChangeCode);
			$output->writeln(sprintf('DbChange %s .', $dbChangeCode, $environment->getCode()));
		} catch (DbChangeException $e) {
			$output->writeln($e->getMessage());
		} catch (EnvironmentException $e) {
			$output->writeln($e->getMessage());
		}
	}
}