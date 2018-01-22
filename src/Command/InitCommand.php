<?php

namespace Kapcus\DbChanger\Command;

use Kapcus\DbChanger\Model\Exception\DbChangeException;
use Kapcus\DbChanger\Model\Exception\EnvironmentException;
use Kapcus\DbChanger\Model\IDescriptor;
use Kapcus\DbChanger\Model\IGenerator;
use Kapcus\DbChanger\Model\ILoader;
use Kapcus\DbChanger\Model\DibiStorage;
use Kapcus\DbChanger\Model\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
	 * @var \Kapcus\DbChanger\Model\IDescriptor
	 */
	public $environmentDescriptor;

	public function __construct(ILoader $loader, Manager $manager, IDescriptor $environmentDescriptor) {
		$this->loader = $loader;
		$this->manager = $manager;
		$this->environmentDescriptor = $environmentDescriptor;
		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName('dbchanger:init')
			->setDescription('Initialize environment by definition in config file')
			->addArgument('env', InputArgument::REQUIRED, 'Target environment code');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$environmentCode = strtoupper($input->getArgument('env'));

		if (($environment = $this->environmentDescriptor->getEnvironmentByCode($environmentCode)) === null) {
			//throw new EnvironmentException(sprintf('Unknown environment code %1$s, ensure this environment is defined in your configuration.', $environmentCode));
			$output->write(sprintf('Unknown environment code %1$s, ensure this environment is defined in your configuration.', $environmentCode));
		} else {
			try {
				$this->manager->initializeEnvironment($environment);
				$output->write(sprintf('Environment %s successfully initialized.', $environment->getCode()));
			} catch (DbChangeException $e) {
				$output->write(sprintf('Environment %s is already initialized.', $environment->getCode()));
			}
		}
	}
}