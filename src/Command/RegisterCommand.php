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
			->setName('dbchanger:register')
			->setDescription('Register DbChange for further installation')
			->addArgument('code', InputArgument::REQUIRED, 'DbChange code to be registered.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$dbChangeCode = strtoupper($input->getArgument('code'));

		try {
			$this->manager->registerDbChangeByCode($dbChangeCode);
			$output->write(sprintf('DbChange %s successfully registered.', $dbChangeCode));
		} catch (DbChangeException $e) {
			$output->write($e->getMessage());
		}
	}
}