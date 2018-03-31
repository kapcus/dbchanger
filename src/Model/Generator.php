<?php

namespace Kapcus\DbChanger\Model;

use Kapcus\DbChanger\Entity\DbChange;
use Kapcus\DbChanger\Entity\Environment;
use Kapcus\DbChanger\Entity\Fragment;
use Kapcus\DbChanger\Entity\UserGroup;
use Kapcus\DbChanger\Model\Exception\GeneratorException;

class Generator implements IGenerator
{
	/**
	 * @var string directory where all data will be generated into
	 */
	private $rootOutputDirectory;

	/**
	 * @var string directory where all data for current command will be generated into
	 */
	private $outputDirectory;

	/**
	 * @var string
	 */
	private $masterFilename = 'complete.sql';

	/**
	 * @var
	 */
	private $masterPathname;

	/**
	 * @var \Kapcus\DbChanger\Model\IDatabase
	 */
	private $database;

	/**
	 * @var \Kapcus\DbChanger\Model\IParser
	 */
	private $parser;

	/**
	 * @var \DateTime
	 */
	private $folderTimestamp;

	public function __construct($outputDirectory, IDatabase $database, IParser $parser)
	{
		$this->database = $database;

		$this->prepareDirectory($outputDirectory);
		$this->rootOutputDirectory = $outputDirectory;
		$this->parser = $parser;

		$this->folderTimestamp = new \DateTime();
	}

	private function prepareDirectory($directory) {
		if (!file_exists($directory) && !mkdir($directory)) {
			throw new GeneratorException(sprintf('Unable to create directory %1$s.', $directory));
		}
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\DbChange[] $dbChanges
	 *
	 * @throws \Kapcus\DbChanger\Model\Exception\GeneratorException
	 */
	public function generateDbChangesIntoFile(Environment $environment, array $dbChanges)
	{
		foreach($dbChanges as $dbChange) {
			$this->generateDbChangeIntoFile($environment, $dbChange);
		}
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\DbChange $dbChange
	 *
	 * @throws \Kapcus\DbChanger\Model\Exception\GeneratorException
	 */
	public function generateDbChangeIntoFile(Environment $environment, DbChange $dbChange)
	{
		foreach($dbChange->getFragments() as $dbChangeFragment) {
			$this->generateFragmentIntoFile($environment, $dbChangeFragment);
		}
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\Fragment $fragment
	 *
	 * @throws \Kapcus\DbChanger\Model\Exception\GeneratorException
	 */
	public function generateFragmentIntoFile(Environment $environment, Fragment $fragment)
	{
		$dbChangeDirectory = $this->prepareDbChangeOutputDirectory($environment, $fragment->getDbChange()->getCode());

		$content = '';
		foreach ($environment->getUserGroupsByGroup($fragment->getGroup()) as $userGroup) {
			$content .= $this->doGenerateFragmentContent($environment, $fragment, $userGroup, true);
		}

		$filename = $dbChangeDirectory.DIRECTORY_SEPARATOR.$fragment->getFilename();
		if ($content != '') {
			file_put_contents($filename, $content);
			file_put_contents($this->masterPathname, $content, FILE_APPEND);
		}
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 *
	 * @return string
	 * @throws \Kapcus\DbChanger\Model\Exception\GeneratorException
	 */
	private function prepareOutputDirectory(Environment $environment) {
		$outputDirectory = $this->rootOutputDirectory.DIRECTORY_SEPARATOR.$this->folderTimestamp->format('Ymd_His').'_'.$environment->getCode();
		$this->masterPathname = $outputDirectory.DIRECTORY_SEPARATOR.$this->masterFilename;
		$this->prepareDirectory($outputDirectory);
		$this->setOutputDirectory($outputDirectory);
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param string $dbChangeCode
	 *
	 * @return string
	 * @throws \Kapcus\DbChanger\Model\Exception\GeneratorException
	 */
	private function prepareDbChangeOutputDirectory(Environment $environment, $dbChangeCode) {
		$this->prepareOutputDirectory($environment);
		$dbChangeDirectory = $this->getOutputDirectory().DIRECTORY_SEPARATOR.$dbChangeCode;
		$this->prepareDirectory($dbChangeDirectory);
		return $dbChangeDirectory;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\Fragment $dbChangeFragment
	 * @param \Kapcus\DbChanger\Entity\UserGroup $userGroup
	 *
	 * @return string
	 */
	public function getFragmentContent(Environment $environment, Fragment $dbChangeFragment, UserGroup $userGroup) {
		return $this->doGenerateFragmentContent($environment, $dbChangeFragment, $userGroup, false);
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\Fragment $dbChangeFragment
	 * @param \Kapcus\DbChanger\Entity\UserGroup $userGroup
	 * @param bool $loadFromFile
	 *
	 * @return string
	 */
	public function doGenerateFragmentContent(Environment $environment, Fragment $dbChangeFragment, UserGroup $userGroup, $loadFromFile = false) {

		$contentTemplate = $loadFromFile ? $dbChangeFragment->getTemplateContentFromFile() : $dbChangeFragment->getTemplateContent();
		$contentTemplate = $this->replacePlaceholders($environment, $contentTemplate);

		$chunks = [];

		$chunks[] = sprintf('-- DbChange: %1$s, Index: %2$s, User: %3$s, Group: %4$s%5$s', $dbChangeFragment->getDbChange()->getCode(), Util::getFragmentIndex($dbChangeFragment->getIndex()), $userGroup->getUser()->getName(), $dbChangeFragment->getGroup()->getName(), $loadFromFile ? '' : ', ID: '.Util::getFragmentId($dbChangeFragment->getId()));

		$chunks[] = $this->addStatementIntoStack($this->database->getChangeUserSql($userGroup->getUser()->getName()));
		//$this->parser->applyOnEachStatement($contentTemplate, [$this, 'replaceGroupPlaceholders']);
		$statements = $this->parser->getStatements($contentTemplate);

		foreach($statements as $statement) {
			$newChunks = $this->replaceGroupPlaceholders($environment, $statement);
			foreach($newChunks as $newChunk) {
				$chunks[] = $this->addStatementIntoStack($newChunk);
			}
		}
		$chunks[] = $this->addStatementIntoStack('COMMIT');
		$chunks[] = '';


		return implode("\n", $chunks);
	}

	private function addStatementIntoStack($statement) {
		return $statement.$this->parser->getDelimiter();
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param string $content
	 *
	 * @return string
	 */
	private function replacePlaceholders(Environment $environment, $content) {
		$codes = [];
		$values = [];
		foreach($environment->getPlaceholders() as $placeholder) {
			$codes[] = $placeholder->getCode();
			$values[] = $placeholder->getTranslatedValue();
		}
		return str_replace($codes, $values, $content);
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param string $statement
	 *
	 * @return string[]
	 */
	private function replaceGroupPlaceholders(Environment $environment, $statement) {
		$newStatements = [];
		foreach ($environment->getGroupNames() as $groupName) {
			if (strpos($statement, $this->getGroupPlaceholder($groupName)) !== false) {
				foreach (Util::getUserGroupUsersByGroupName($environment->getUserGroups(), $groupName) as $user) {
					$newStatements[] = str_replace($this->getGroupPlaceholder($groupName), $user->getName(), $statement);
				}
				return $newStatements;
			}
		}

		$newStatements[] = $statement;

		return $newStatements;
	}

	/**
	 * @param string $groupName
	 *
	 * @return string
	 */
	private function getGroupPlaceholder($groupName) {
		return sprintf('<%s>', $groupName);
	}

	/**
	 * @return string
	 */
	public function getOutputDirectory()
	{
		return $this->outputDirectory;
	}

	/**
	 * @param string $outputDirectory
	 */
	private function setOutputDirectory($outputDirectory)
	{
		$this->outputDirectory = $outputDirectory;
	}


}