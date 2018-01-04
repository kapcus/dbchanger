<?php

namespace Kapcus\DbChanger\Command;

use Kapcus\DbChanger\Model\Exception\EnvironmentException;
use Kapcus\DbChanger\Model\IDescriptor;
use Kapcus\DbChanger\Model\IGenerator;
use Kapcus\DbChanger\Model\ILoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command
{
	/**
	 * @var \Kapcus\DbChanger\Model\ILoader
	 */
	public $loader;

	/**
	 * @var \Kapcus\DbChanger\Model\IGenerator
	 */
	public $generator;

	/**
	 * @var \Kapcus\DbChanger\Model\IDescriptor
	 */
	public $environmentDescriptor;

	public function __construct(ILoader $loader, IGenerator $generator, IDescriptor $environmentDescriptor) {
		$this->loader = $loader;
		$this->generator = $generator;
		$this->environmentDescriptor = $environmentDescriptor;
		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName('dbchanger:generate')
			->setDescription('Generate DbChange sql content from templates defined in given folder')
			->addArgument('env', InputArgument::REQUIRED, 'Target environment code');
			/*->addOption('backwards', 'b', null, 'Pozpátku?')
			->addOption('greeting', null, InputOption::VALUE_REQUIRED, 'Pozdrav', 'Hello');*/
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$environmentCode = strtoupper($input->getArgument('env'));

		if (($environment = $this->environmentDescriptor->getEnvironmentByCode($environmentCode)) === null) {
			throw new EnvironmentException(sprintf('Unknown environment code %1$s, ensure this environment is defined in your configuration.', $environmentCode));
		}
		$dbChanges = $this->loader->loadDbChanges();
		foreach($dbChanges as $dbChange) {
			$this->generator->generateDbChange($environment, $dbChange);
		}
	}
}