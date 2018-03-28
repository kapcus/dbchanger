<?php

namespace Kapcus\DbChanger\Command;

use Kapcus\DbChanger\Model\Exception\EnvironmentException;
use Kapcus\DbChanger\Model\IConfigurator;
use Kapcus\DbChanger\Model\IGenerator;
use Kapcus\DbChanger\Model\ILoader;
use Kapcus\DbChanger\Model\Manager;
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
	 * @var \Kapcus\DbChanger\Model\Manager
	 */
	public $manager;


	/**
	 * @var \Kapcus\DbChanger\Model\IConfigurator
	 */
	public $configurator;

	public function __construct(ILoader $loader, IGenerator $generator, IConfigurator $configurator, Manager $manager) {
		$this->loader = $loader;
		$this->generator = $generator;
		$this->configurator = $configurator;
		$this->manager = $manager;
		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName('dbchanger:generate')
			->setDescription('Generate DbChange sql content from templates defined in given folder.')
			->addArgument('env', InputArgument::REQUIRED, 'Target environment code')
			->addArgument('dbchange', InputArgument::OPTIONAL, 'DbChange code of the dbChange to be generated')
			->addArgument('fragmentIndex', InputArgument::OPTIONAL, 'Fragment index of the fragment to be generated');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$environmentCode = strtoupper($input->getArgument('env'));
		$dbchangeCode = strtoupper($input->getArgument('dbchange'));
		$fragmentIndex = intval($input->getArgument('fragmentIndex'));

		$outputDirectory = '';
		try {
			$environment = $this->manager->getEnvironmentByCode($environmentCode);

			$dbChanges = $this->loader->loadDbChangesFromInputDirectory($this->manager->getGroups());
			if (isset($dbchangeCode)) {
				foreach($dbChanges as $dbChange) {
					if ($dbChange->getCode() == $dbchangeCode) {
						if (isset($fragmentIndex)) {
							foreach($dbChange->getFragments() as $fragment) {
								if ($fragment->getIndex() == $fragmentIndex) {
									$outputDirectory = $this->generator->generateFragment($environment, $fragment);
									break;
								}
							}
						} else {
							$outputDirectory = $this->generator->generateDbChange($environment, $dbChange);
						}
						break;
					}
				}
			} else {
				$outputDirectory = $this->generator->generateDbChanges($environment, $dbChanges);
			}
			$output->writeln(sprintf(' Requested content successfully generated into output subdirectory %s.', $outputDirectory));
		} catch (EnvironmentException $e) {
			$output->writeln($e->getMessage().' Consider running "reinit" command.');
		}
	}
}