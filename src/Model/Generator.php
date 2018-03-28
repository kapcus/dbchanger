<?php

namespace Kapcus\DbChanger\Model;

use Doctrine\Common\Util\Debug;
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
		$this->outputDirectory = $outputDirectory;
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
	 * @return string
	 * @throws \Kapcus\DbChanger\Model\Exception\GeneratorException
	 */
	public function generateDbChanges(Environment $environment, array $dbChanges)
	{
		$outputDirectory = $this->prepareGenerateCommandDirectory($environment);
		foreach($dbChanges as $dbChange) {
			$dbChangeDirectory = $outputDirectory.DIRECTORY_SEPARATOR.$dbChange->getCode();
			$this->prepareDirectory($dbChangeDirectory);
			foreach($dbChange->getFragments() as $dbChangeFragment) {
				$this->generateDbChangeFragmentIntoFile($environment, $dbChangeFragment, $dbChangeDirectory);
			}
		}

		return $outputDirectory;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\DbChange $dbChange
	 *
	 * @return string
	 * @throws \Kapcus\DbChanger\Model\Exception\GeneratorException
	 */
	public function generateDbChange(Environment $environment, DbChange $dbChange)
	{
		$outputDirectory = $this->prepareGenerateCommandDirectory($environment);
		$dbChangeDirectory = $outputDirectory.DIRECTORY_SEPARATOR.$dbChange->getCode();
		$this->prepareDirectory($dbChangeDirectory);
		foreach($dbChange->getFragments() as $dbChangeFragment) {
			$this->generateDbChangeFragmentIntoFile($environment, $dbChangeFragment, $dbChangeDirectory);
		}

		return $outputDirectory;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\Fragment $fragment
	 *
	 * @return string
	 * @throws \Kapcus\DbChanger\Model\Exception\GeneratorException
	 */
	public function generateFragment(Environment $environment, Fragment $fragment)
	{
		$outputDirectory = $this->prepareGenerateCommandDirectory($environment);
		$dbChangeDirectory = $outputDirectory.DIRECTORY_SEPARATOR.$fragment->getDbChange()->getCode();
		$this->prepareDirectory($dbChangeDirectory);
		$this->generateDbChangeFragmentIntoFile($environment, $fragment, $dbChangeDirectory);

		return $outputDirectory;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 *
	 * @return string
	 * @throws \Kapcus\DbChanger\Model\Exception\GeneratorException
	 */
	private function prepareGenerateCommandDirectory(Environment $environment) {
		$dbChangesDirectory = $this->outputDirectory.DIRECTORY_SEPARATOR.$this->folderTimestamp->format('Ymd_His').'_'.$environment->getCode();
		$this->masterPathname = $dbChangesDirectory.DIRECTORY_SEPARATOR.$this->masterFilename;
		$this->prepareDirectory($dbChangesDirectory);
		return $dbChangesDirectory;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\Fragment $dbChangeFragment
	 * @param string $dbChangeDirectory
	 */
	private function generateDbChangeFragmentIntoFile(Environment $environment, \Kapcus\DbChanger\Entity\Fragment $dbChangeFragment, $dbChangeDirectory)
	{
		$filename = $dbChangeDirectory.DIRECTORY_SEPARATOR.$dbChangeFragment->getFilename();
		$contentTemplate = $dbChangeFragment->getTemplateContentFromFile();
		$contentTemplate = $this->replacePlaceholders($environment, $contentTemplate);
		$chunks = [];

		$chunks[] = sprintf('-- %1$s %2$s', $dbChangeFragment->getDbChange()->getCode(), $dbChangeFragment->getGroup()->getName());

		$users = Util::getUserGroupUsersByGroupName($environment->getUserGroups(), $dbChangeFragment->getGroup()->getName());

		$statements = $this->parser->getStatements($contentTemplate);

		foreach($users as $user) {
			$chunks[] = $this->addStatementIntoStack($this->database->getChangeUserSql($user->getName()));
			foreach($statements as $statement) {
				$newChunks = $this->replaceGroupPlaceholders($environment, $statement);
				foreach($newChunks as $newChunk) {
					$chunks[] = $this->addStatementIntoStack($newChunk);
				}
			}
			$chunks[] = $this->addStatementIntoStack('COMMIT');
			$chunks[] = '';
		}
		if (!empty($chunks)) {
			file_put_contents($filename, implode("\n", $chunks));
			file_put_contents($this->masterPathname, implode("\n", $chunks), FILE_APPEND);
		}
	}

	private function addStatementIntoStack($statement) {
		return $statement.$this->parser->getDelimiter();
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\Fragment $dbChangeFragment
	 * @param \Kapcus\DbChanger\Entity\UserGroup $userGroup
	 *
	 * @return string
	 */
	public function generateDbChangeFragmentContent(Environment $environment, \Kapcus\DbChanger\Entity\Fragment $dbChangeFragment, UserGroup $userGroup) {
		$contentTemplate = $dbChangeFragment->getTemplateContent();
		$contentTemplate = $this->replacePlaceholders($environment, $contentTemplate);

		$chunks = [];

		$chunks[] = sprintf('-- %1$s : %2$s : %3$s', $dbChangeFragment->getDbChange()->getCode(), $dbChangeFragment->getGroup()->getName(), $userGroup->getUser()->getName());

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


		return implode("\n", $chunks);
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

}