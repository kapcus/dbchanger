<?php

namespace Kapcus\DbChanger\Command;

use Kapcus\DbChanger\Model\Exception\DbChangeException;
use Kapcus\DbChanger\Model\Exception\EnvironmentException;
use Kapcus\DbChanger\Model\IConfigurator;
use Kapcus\DbChanger\Model\IGenerator;
use Kapcus\DbChanger\Model\ILoader;
use Kapcus\DbChanger\Model\Manager;
use Kapcus\DbChanger\Model\Util;
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
		$dbChangeCode = strtoupper($input->getArgument('dbchange'));
		$fragmentIndex = $input->getArgument('fragmentIndex');
		$fragmentId = Util::getIndexFromFragmentIndex($fragmentIndex);

		try {
			$environment = $this->manager->getEnvironmentByCode($environmentCode);
			if ($dbChangeCode != '') {
				$dbChange = $this->loader->loadDbChangeFromInputDirectory($this->manager->getGroups(), $dbChangeCode);
				if ($fragmentId !== null) {
					$found = false;
					foreach($dbChange->getFragments() as $fragment) {
						if ($fragment->getIndex() == $fragmentId) {
							$found = true;
							$this->generator->generateFragmentIntoFile($environment, $fragment);
							break;
						}
					}
					if (!$found) {
						throw new DbChangeException('Fragment not found.');
					}
				} else {
					$this->generator->generateDbChangeIntoFile($environment, $dbChange);
				}
			} else {
				$dbChanges = $this->loader->loadDbChangesFromInputDirectory($this->manager->getGroups());
				$this->generator->generateDbChangesIntoFile($environment, $dbChanges);
			}
			$output->writeln(sprintf('Requested content successfully generated into output subdirectory %s.', $this->generator->getOutputDirectory()));
		} catch (EnvironmentException $e) {
			$output->writeln($e->getMessage().' Consider running "reinit" command.');
		} catch (DbChangeException $e) {
			$output->writeln($e->getMessage().' Consider running "reinit" command.');
		}
	}
}