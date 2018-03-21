<?php

namespace Kapcus\DbChanger\Model;

use Kapcus\DbChanger\Entity\Environment;
use Kapcus\DbChanger\Model\Exception\GeneratorException;

class Generator implements IGenerator
{
	/**
	 * @var string directory where all data will be generated into
	 */
	private $outputDirectory;
	private $currentUserPlaceholder = '<region>';
	private $overrideDirectories = true;
	private $dbChangeDirectory;
	private $dbChangeEnvironmentDirectory;

	/**
	 * @var \Kapcus\DbChanger\Model\IDatabase
	 */
	private $database;

	public function __construct($outputDirectory, IDatabase $database)
	{
		$this->database = $database;
		$this->outputDirectory = $outputDirectory;
	}

	public function generateDbChange(Environment $environment, \Kapcus\DbChanger\Entity\DbChange $dbChange)
	{
		if (!file_exists($this->outputDirectory) && !mkdir($this->outputDirectory)) {
			throw new GeneratorException(sprintf('Unable to create output directory %1$s.', $this->outputDirectory));
		}

		$this->dbChangeDirectory = $this->outputDirectory.DIRECTORY_SEPARATOR.$dbChange->getCode();
		$this->dbChangeEnvironmentDirectory = $this->dbChangeDirectory.DIRECTORY_SEPARATOR.$environment->getCode();

		$this->prepareDirectory($environment, $dbChange);
		foreach($dbChange->getFragments() as $dbChangeFragment) {
			$this->generateDbChangeFragment($environment, $dbChangeFragment);
		}

	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\DbChange $dbChange
	 *
	 * @return void
	 * @throws \Kapcus\DbChanger\Model\Exception\GeneratorException
	 */
	private function prepareDirectory(Environment $environment, \Kapcus\DbChanger\Entity\DbChange $dbChange)
	{
		if (!file_exists($this->dbChangeDirectory) && !mkdir($this->dbChangeDirectory)) {
			throw new GeneratorException(sprintf('Unable to create directory %2$s for DbChange %1$s.', $dbChange->getCode(), $this->dbChangeDirectory));
		}
		if (file_exists($this->dbChangeEnvironmentDirectory)) {
			if (!$this->overrideDirectories) {
				throw new GeneratorException(sprintf('DbChange %1$s is already generated for environment %2$s (%3$s).', $dbChange->getCode(), $environment->getName(), $environment->getCode()));
			} else {
				$files = glob($this->dbChangeEnvironmentDirectory . DIRECTORY_SEPARATOR . '*');
				foreach($files as $file){
					if(is_file($file)){
						unlink($file);
					}
				}
				if (!rmdir($this->dbChangeEnvironmentDirectory)) {
					throw new GeneratorException(sprintf('Unable to delete directory %4$s for DbChange %1$s for environment %2$s (%3$s).', $dbChange->getCode(), $environment->getName(), $environment->getCode(), $this->dbChangeEnvironmentDirectory));
				}
			}
		}
		if (!mkdir($this->dbChangeEnvironmentDirectory)) {
			throw new GeneratorException(
				sprintf(
					'Unable to create sub-directory %4$s for DbChange %1$s for environment %2$s (%3$s).',
					$dbChange->getCode(),
					$environment->getName(),
					$environment->getCode(),
					$this->dbChangeEnvironmentDirectory
				)
			);
		}
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\Fragment $dbChangeFragment
	 */
	private function generateDbChangeFragment(Environment $environment, \Kapcus\DbChanger\Entity\Fragment $dbChangeFragment)
	{
		$filename = $this->dbChangeEnvironmentDirectory.DIRECTORY_SEPARATOR.$dbChangeFragment->getFilename();
		$contentTemplate = $dbChangeFragment->getTemplateContentFromFile();
		$contentTemplate = $this->replacePlaceholders($environment, $contentTemplate);
		$chunks = [];

		$chunks[] = sprintf('-- %1$s %2$s', $dbChangeFragment->getDbChange()->getCode(), $dbChangeFragment->getGroup()->getName());
		$chunks[] = '';

		$users = Util::getUsersFromUserGroup($environment->getUserGroups(), $dbChangeFragment->getGroup()->getName());

		foreach($users as $user) {
			$chunks[] = $this->database->getChangeUserSql($user->getName());
			$chunks[] = str_replace($this->currentUserPlaceholder, $user->getName(), $contentTemplate);
			$chunks[] = 'COMMIT;';
			$chunks[] = '';
		}
		if (!empty($chunks)) {
			file_put_contents($filename, implode("\n", $chunks));
		}
	}

	/*public function generateFragmentContent(Environment $environment, Fragment $dbChangeFragment) {
		$contentTemplate = $dbChangeFragment->getTemplate();
		$contentTemplate = $this->replacePlaceholders($environment, $contentTemplate);

		foreach($environment->getUsersInGroup($dbChangeFragment->getGroup()->getName()) as $user) {
			$chunks = [];
			$chunks[] = $this->database->getChangeUserSql($user->getName()).';';
			$chunks[] = str_replace($this->currentUserPlaceholder, $user->getName(), $contentTemplate);
			$chunks[] = '';
			$dbChangeFragment->setUserContent($user, implode("\n", $chunks));
		}
		/*if (!empty($chunks)) {
			$dbChangeFragment->setContent(implode("\n", $chunks));
		}*/
	//}

	private function replacePlaceholders(Environment $environment, $content) {
		return str_replace($environment->getPlaceholderCodes(), $environment->getPlaceholderValues(), $content);
	}

}