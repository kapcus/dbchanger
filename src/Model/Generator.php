<?php

namespace Kapcus\DbChanger\Model;

use Kapcus\DbChanger\Model\Exception\GeneratorException;

class Generator implements IGenerator
{

	private $outputDirectory = 'c:\workgit\FSI\webfe\temp';
	private $currentUserPlaceholder = '/*region*/';
	private $overrideDirectories = true;
	private $dbChangeDirectory;
	private $dbChangeEnvironmentDirectory;

	/**
	 * @var \Kapcus\DbChanger\Model\IDatabase
	 */
	private $database;

	public function __construct(IDatabase $database)
	{
		$this->database = $database;
	}

	public function generateDbChange(Environment $environment, DbChange $dbChange)
	{
		$this->dbChangeDirectory = $this->outputDirectory.DIRECTORY_SEPARATOR.$dbChange->getCode();
		$this->dbChangeEnvironmentDirectory = $this->dbChangeDirectory.DIRECTORY_SEPARATOR.$environment->getCode();

		$this->prepareDirectory($environment, $dbChange);
		foreach($dbChange->getFragments() as $dbChangeFragment) {
			$this->generateDbChangeFragment($environment, $dbChangeFragment);
		}

	}

	/**
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 * @param \Kapcus\DbChanger\Model\DbChange $dbChange
	 *
	 * @return void
	 * @throws \Kapcus\DbChanger\Model\Exception\GeneratorException
	 */
	private function prepareDirectory(Environment $environment, DbChange $dbChange)
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
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 * @param \Kapcus\DbChanger\Model\DbChangeFragment $dbChangeFragment
	 */
	private function generateDbChangeFragment(Environment $environment, DbChangeFragment $dbChangeFragment)
	{
		$filename = $this->dbChangeEnvironmentDirectory.DIRECTORY_SEPARATOR.$dbChangeFragment->getFilename();
		$contentTemplate = $dbChangeFragment->getContent();
		$contentTemplate = $this->replacePlaceholders($environment, $contentTemplate);
		$chunks = [];

		foreach($environment->getUsersInGroup($dbChangeFragment->getGroup()->getName()) as $user) {
			$chunks[] = $this->database->getChangeUserSql($user->getName());
			$chunks[] = str_replace($this->currentUserPlaceholder, $user->getName(), $contentTemplate);
			$chunks[] = '';
		}
		if (!empty($chunks)) {
			file_put_contents($filename, implode("\n", $chunks));
		}
	}

	private function replacePlaceholders(Environment $environment, $content) {
		return str_replace($environment->getPlaceholderCodes(), $environment->getPlaceholderValues(), $content);
	}
}