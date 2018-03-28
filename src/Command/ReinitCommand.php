<?php

namespace Kapcus\DbChanger\Command;

use Kapcus\DbChanger\Model\Exception\OutOfSyncException;
use Kapcus\DbChanger\Model\IConfigurator;
use Kapcus\DbChanger\Model\ILoader;
use Kapcus\DbChanger\Model\Manager;
use Nette\NotImplementedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReinitCommand extends Command
{

	public function __construct() {

		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName('dbchanger:reinit')
			->setDescription('Reinitialize environments defined in config file.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		throw new NotImplementedException();
	}
}