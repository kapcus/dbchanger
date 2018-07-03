<?php

namespace Kapcus\DbChanger\Command;

use Doctrine\Common\Util\Debug;
use Kapcus\DbChanger\Model\Exception\DbChangeException;
use Kapcus\DbChanger\Model\Exception\EnvironmentException;
use Kapcus\DbChanger\Model\Exception\InstallationException;
use Kapcus\DbChanger\Model\Executor;
use Kapcus\DbChanger\Model\IConfigurator;
use Kapcus\DbChanger\Model\IExecutor;
use Kapcus\DbChanger\Model\ILoader;
use Kapcus\DbChanger\Model\DibiStorage;
use Kapcus\DbChanger\Model\Manager;
use Kapcus\DbChanger\Model\Reporting\Row;
use Kapcus\DbChanger\Model\Reporting\Table;
use Nette\NotImplementedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends Command
{

	const FIELD_WIDTH = 15;
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

	public function __construct(ILoader $loader, Manager $manager, IConfigurator $configurator)
	{
		$this->loader = $loader;
		$this->manager = $manager;
		$this->configurator = $configurator;
		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName('dbchanger:status')
			->setDescription('Checks whether given DbChange is installed on particular environment.')
			->addArgument('env', InputArgument::REQUIRED, 'Target environment code')
			->addArgument('dbChangeCode', InputArgument::REQUIRED, 'DbChange code to be checked');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$environmentCode = strtoupper($input->getArgument('env'));

		$dbChangeCode = strtoupper($input->getArgument('dbChangeCode'));

		try {
			$environment = $this->manager->getEnvironmentByCode($environmentCode);
			$output->writeln('');
			$output->writeln(sprintf('Installations for Environment %s and DbChange %s:', $environment->getCode(), $dbChangeCode));
			$reportData = $this->manager->getDbChangeReport($environment, $dbChangeCode);
			$this->displayTable($output, $reportData['installations']);
			$output->writeln('');
			$output->writeln('');
			foreach($reportData['activeinstallations'] as $installationId => $table) {
				$output->writeln(sprintf('Fragments in installation %s:', $installationId));
				$this->displayTable($output, $table);
				$output->writeln('');
				$output->writeln('');
			}
			exit(0);
		} catch (DbChangeException $e) {
			$output->writeln($e->getMessage());
		} catch (InstallationException $e) {
			$output->writeln($e->getMessage());
		} catch (EnvironmentException $e) {
			$output->writeln($e->getMessage());
		}
		exit(1);
	}

	private function displayTable(OutputInterface $output, Table $table)
	{
		$this->writeTableSeparator($output, $table->getWidth());
		foreach ($table->getRows() as $row) {
			$output->writeln($this->formatTableRow($row));
			if ($row->isHeader()) {
				$this->writeTableSeparator($output, $table->getWidth());
			}
		}
	}

	private function writeTableSeparator(OutputInterface $output, $width) {
		$output->writeln(str_pad('',  $width, "-"));
	}

	private function formatTableRow(Row $row)
	{
		$chunks = [];
		foreach($row->getCells() as $cell) {
			$tags = sprintf($row->isHeader() ? '<info>' : '');
			$chunks[] = $tags.sprintf('%'.$cell->getColumn()->getWidth().'s', $row->isHeader() ? $cell->getColumn()->getTitle() : $cell->getValue()).$tags;
		}
		return implode(' | ', $chunks);
	}
}