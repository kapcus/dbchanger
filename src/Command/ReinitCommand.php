<?php

namespace Kapcus\DbChanger\Command;

use Nette\NotImplementedException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReinitCommand extends FormattedOutputCommand
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