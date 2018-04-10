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
	const PLACEHOLDER_START = '/*START*/';

	const PLACEHOLDER_END = '/*END*/';

	const PLACEHOLDER_GLUE_START = '/*GLUE_START ';

	const PLACEHOLDER_GLUE_END = 'GLUE_END*/';

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

	/**
	 * @var bool
	 */
	private $isDebug = false;

	public function __construct($outputDirectory, IDatabase $database, IParser $parser)
	{
		$this->database = $database;

		$this->prepareDirectory($outputDirectory);
		$this->rootOutputDirectory = $outputDirectory;
		$this->parser = $parser;

		$this->folderTimestamp = new \DateTime();
	}

	private function prepareDirectory($directory)
	{
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
		foreach ($dbChanges as $dbChange) {
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
		foreach ($dbChange->getFragments() as $dbChangeFragment) {
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

		$filename = $dbChangeDirectory . DIRECTORY_SEPARATOR . $fragment->getFilename();
		if ($content != '') {
			file_put_contents($filename, $content);
			file_put_contents($this->masterPathname, $content, FILE_APPEND);
			if ($this->isDebug) {
				var_dump($content);
			}
		}
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 *
	 * @return string
	 * @throws \Kapcus\DbChanger\Model\Exception\GeneratorException
	 */
	private function prepareOutputDirectory(Environment $environment)
	{
		$outputDirectory = $this->rootOutputDirectory . DIRECTORY_SEPARATOR . $this->folderTimestamp->format('Ymd_His') . '_' . $environment->getCode(
			);
		$this->masterPathname = $outputDirectory . DIRECTORY_SEPARATOR . $this->masterFilename;
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
	private function prepareDbChangeOutputDirectory(Environment $environment, $dbChangeCode)
	{
		$this->prepareOutputDirectory($environment);
		$dbChangeDirectory = $this->getOutputDirectory() . DIRECTORY_SEPARATOR . $dbChangeCode;
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
	public function getFragmentContent(Environment $environment, Fragment $dbChangeFragment, UserGroup $userGroup)
	{
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
	public function doGenerateFragmentContent(Environment $environment, Fragment $dbChangeFragment, UserGroup $userGroup, $loadFromFile = false)
	{

		$contentTemplate = $loadFromFile ? $dbChangeFragment->getTemplateContentFromFile() : $dbChangeFragment->getTemplateContent();
		$contentTemplate = $this->replacePlaceholders($environment, $contentTemplate);

		$chunks = [];

		$chunks[] = sprintf(
			'-- DbChange: %1$s, Index: %2$s, User: %3$s, Group: %4$s%5$s',
			$dbChangeFragment->getDbChange()->getCode(),
			Util::getFragmentIndex($dbChangeFragment->getIndex()),
			$userGroup->getUser()->getName(),
			$dbChangeFragment->getGroup()->getName(),
			$loadFromFile ? '' : ', ID: ' . Util::getFragmentId($dbChangeFragment->getId())
		);

		$chunks[] = $this->database->getChangeUserSql($userGroup->getUser()->getName()) . $this->parser->getDelimiter();
		//$this->parser->applyOnEachStatement($contentTemplate, [$this, 'replaceGroupPlaceholders']);
		$statements = $this->parser->parseContent($contentTemplate);

		foreach ($statements as $statement) {
			$newChunks = $this->replaceGroupPlaceholder($environment, $statement->getContent());
			foreach ($newChunks as $newChunk) {
				$chunks[] = sprintf('%s%s', $newChunk, $statement->getDelimiter());
			}
		}
		$chunks[] = 'COMMIT' . $this->parser->getDelimiter();
		$chunks[] = '';

		return implode("\n", $chunks);
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param string $content
	 *
	 * @return string
	 */
	private function replacePlaceholders(Environment $environment, $content)
	{
		$codes = [];
		$values = [];
		foreach ($environment->getPlaceholders() as $placeholder) {
			$codes[] = $placeholder->getCode();
			$values[] = $placeholder->getTranslatedValue();
		}

		return str_replace($codes, $values, $content);
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param string $statementContent
	 *
	 * @return string[]
	 */
	private function replaceGroupPlaceholder(Environment $environment, $statementContent)
	{
		if (strpos($statementContent, self::PLACEHOLDER_START) !== false) {
			return $this->replaceGroupPlaceholderInSubstring($environment, $statementContent);
		} else {
			return $this->replaceGroupPlaceholderGlobally($environment, $statementContent);
		}
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param $statementContent
	 *
	 * @return string[]
	 */
	private function replaceGroupPlaceholderGlobally(Environment $environment, $statementContent) {
		$newStatements = [];
		foreach ($environment->getGroupNames() as $groupName) {
			if (strpos($statementContent, $this->getGroupPlaceholder($groupName)) !== false) {
				foreach (Util::getUserGroupUsersByGroupName($environment->getUserGroups(), $groupName) as $user) {
					$newStatements[] = str_replace(
						$this->getGroupPlaceholder($groupName),
						$user->getName(),
						$statementContent
					);
				}
				return $newStatements;
			}
		}
		return [$statementContent];
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param $statementContent
	 *
	 * @return string[]
	 */
	private function replaceGroupPlaceholderInSubstring(Environment $environment, $statementContent) {
		$startPlaceholderIndex = strpos($statementContent, self::PLACEHOLDER_START);
		$newStatements = [];
		$subStringLength = null;

		$startIndex = $startPlaceholderIndex + strlen(self::PLACEHOLDER_START);
		$endPlaceholderIndex = strpos($statementContent, self::PLACEHOLDER_END);
		if ($endPlaceholderIndex !== false) {
			$subStringLength = $endPlaceholderIndex - $startIndex;
		}

		foreach ($environment->getGroupNames() as $groupName) {
			if (strpos($statementContent, $this->getGroupPlaceholder($groupName)) !== false) {
				foreach (Util::getUserGroupUsersByGroupName($environment->getUserGroups(), $groupName) as $user) {
					$newStatements[] = str_replace(
						$this->getGroupPlaceholder($groupName),
						$user->getName(),
						substr($statementContent, $startIndex, $subStringLength)
					);
				}

				$glueStartPlaceholderIndex = strpos($statementContent, self::PLACEHOLDER_GLUE_START);
				$endIndex = $endPlaceholderIndex + strlen(self::PLACEHOLDER_END);
				$glue = '';
				if ($glueStartPlaceholderIndex !== false) {
					$glueEndPlaceholderIndex = strpos($statementContent, self::PLACEHOLDER_GLUE_END, $glueStartPlaceholderIndex);
					$endIndex = $glueEndPlaceholderIndex + strlen(self::PLACEHOLDER_GLUE_END);
					$glue = substr(
						$statementContent,
						$glueStartPlaceholderIndex + strlen(self::PLACEHOLDER_GLUE_START),
						$glueEndPlaceholderIndex - $glueStartPlaceholderIndex - strlen(self::PLACEHOLDER_GLUE_START)
					);
				}
				$beginning = substr($statementContent, 0, $startPlaceholderIndex);
				$end = substr($statementContent, $endIndex);

				return [sprintf("%s %s %s", $beginning, implode($glue, $newStatements), $end)];
			}
		}

		return [$statementContent];
	}

	/**
	 * @param string $groupName
	 *
	 * @return string
	 */
	private function getGroupPlaceholder($groupName)
	{
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

	/**
	 * @return void
	 */
	public function enableDebug()
	{
		$this->isDebug = true;
	}
}