<?php

namespace Kapcus\DbChanger\Command;

use Kapcus\DbChanger\Entity\InstalledFragment;
use Kapcus\DbChanger\Model\Exception\DbChangeException;
use Kapcus\DbChanger\Model\Exception\EnvironmentException;
use Kapcus\DbChanger\Model\IConfigurator;
use Kapcus\DbChanger\Model\IGenerator;
use Kapcus\DbChanger\Model\ILoader;
use Kapcus\DbChanger\Model\DibiStorage;
use Kapcus\DbChanger\Model\Manager;
use Kapcus\DbChanger\Model\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MarkCommand extends Command
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
			->setName('dbchanger:mark')
			->setDescription(sprintf('Mark DbChange fragment with given status: %s.', InstalledFragment::getStatusNameString()))
			->addArgument('identificator', InputArgument::REQUIRED, 'Fragment id or fullcode to be marked.')
		->addArgument('status', InputArgument::REQUIRED, 'Status to be set.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$fragmentIdentificator = strtoupper($input->getArgument('identificator'));
		$statusShortcut = strtoupper($input->getArgument('status'));

		try {
			if (Util::isFullCode($fragmentIdentificator)) {
				$this->manager->markFragmentByFullCode($fragmentIdentificator, $statusShortcut);
			} else if (is_numeric($fragmentIdentificator)) {
				$this->manager->markFragmentById($fragmentIdentificator, $statusShortcut);
			} else {
				throw new DbChangeException(sprintf('Invalid identificator %s. Specify either fragment id or full code (see full code format description).', $fragmentIdentificator));
			}
			$output->writeln(sprintf('Fragment %s successfully marked with status %s.', $fragmentIdentificator, InstalledFragment::getStatusName(InstalledFragment::getStatusByShortcut($statusShortcut))));
		} catch (DbChangeException $e) {
			$output->writeln($e->getMessage());
		}
	}
}