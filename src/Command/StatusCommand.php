<?php

namespace Kapcus\DbChanger\Command;

use Kapcus\DbChanger\Model\Exception\DbChangeException;
use Kapcus\DbChanger\Model\Exception\EnvironmentException;
use Kapcus\DbChanger\Model\Exception\InstallationException;
use Kapcus\DbChanger\Model\Manager;
use Kapcus\DbChanger\Model\Util;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends FormattedOutputCommand
{

	/**
	 * @var \Kapcus\DbChanger\Model\Manager
	 */
	public $manager;

	public function __construct(Manager $manager)
	{
		$this->manager = $manager;
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
			foreach($reportData['details'] as $installationId => $table) {
				$output->writeln(sprintf('Fragments in installation %s:', Util::getInstallationId($installationId)));
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
}