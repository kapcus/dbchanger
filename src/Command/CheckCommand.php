<?php

namespace Kapcus\DbChanger\Command;

use Kapcus\DbChanger\Model\Exception\ConfigurationException;
use Kapcus\DbChanger\Model\Exception\DbChangeException;
use Kapcus\DbChanger\Model\IConfigurator;
use Kapcus\DbChanger\Model\Manager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCommand extends FormattedOutputCommand
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
			->setName('dbchanger:check')
			->setDescription('Checks whether DbChanger is properly installed and configured.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		try {
			$this->configurator->getEnvironments();
			$this->manager->checkTables();
			$output->writeln(sprintf('OK: DbChanger seems to be properly configured.'));
		} catch (ConfigurationException $e) {
			$output->writeln(sprintf('FAILURE: %s ', $e->getMessage()));
		} catch (DbChangeException $e) {
			$output->writeln(sprintf('FAILURE: %s', $e->getMessage()));
		} catch (\Exception $e) {
			$output->writeln(sprintf('FAILURE: %s ', $e->getMessage()));
			throw $e;
		}
	}
}