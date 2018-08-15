<?php

namespace Kapcus\DbChanger\Command;

use Kapcus\DbChanger\Model\Exception\DbChangeException;
use Kapcus\DbChanger\Model\Manager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LogCommand extends FormattedOutputCommand
{
	/**
	 * @var \Kapcus\DbChanger\Model\Manager
	 */
	public $manager;

	public function __construct(Manager $manager) {
		$this->manager = $manager;
		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName('dbchanger:log')
			->setDescription(sprintf('Display the log history of DbChange fragment.'))
			->addArgument('fragment', InputArgument::REQUIRED, 'Fragment id.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$fragmentId = strtoupper($input->getArgument('fragment'));

		try {
			$output->writeln('');
			$output->writeln(sprintf('Logs for Installation fragment %s:', $fragmentId));
			$reportData = $this->manager->getInstallationFragmentLogReport($fragmentId);
			$this->displayTable($output, $reportData);
			$output->writeln('');
		} catch (DbChangeException $e) {
			$output->writeln($e->getMessage());
		}
	}
}