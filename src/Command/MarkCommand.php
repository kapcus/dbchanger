<?php

namespace Kapcus\DbChanger\Command;

use Kapcus\DbChanger\Entity\Installation;
use Kapcus\DbChanger\Entity\InstalledFragment;
use Kapcus\DbChanger\Model\Exception\DbChangeException;
use Kapcus\DbChanger\Model\Manager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MarkCommand extends FormattedOutputCommand
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
			->setName('dbchanger:mark')
			->setDescription(sprintf('Mark DbChange fragment(s)/installation with given status: %s / %s.', InstalledFragment::getStatusNameString(), Installation::getStatusNameString()))
			->addArgument('target', InputArgument::REQUIRED, 'Fragment id, fragment range or installation id.')
		->addArgument('status', InputArgument::REQUIRED, 'Status to be set.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$target = strtoupper($input->getArgument('target'));
		$statusShortcut = strtoupper($input->getArgument('status'));

		try {
			$this->manager->markTarget($target, $statusShortcut);
			$output->writeln(sprintf('Target successfully marked with status %s.', InstalledFragment::getStatusName(InstalledFragment::getStatusByShortcut($statusShortcut))));
		} catch (DbChangeException $e) {
			$output->writeln($e->getMessage());
		}
	}
}