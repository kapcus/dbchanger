<?php

namespace Kapcus\DbChanger\Command;

use Kapcus\DbChanger\Model\Exception\DbChangeException;
use Kapcus\DbChanger\Model\Manager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisplayCommand extends FormattedOutputCommand
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
			->setName('dbchanger:display')
			->setDescription(sprintf('Display the content of DbChange fragment or log.'))
			->addArgument('target', InputArgument::REQUIRED, 'Fragment id or log id.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$target = strtoupper($input->getArgument('target'));

		try {
			$dataToDisplay = $this->manager->displayDetail($target);
			$output->writeln('');
			foreach($dataToDisplay as $key => $row) {
				$output->writeln($key.':');
				$output->writeln('------------------');
				$output->writeln($row);
				$output->writeln('------------------');
			}
		} catch (DbChangeException $e) {
			$output->writeln($e->getMessage());
		}
	}
}