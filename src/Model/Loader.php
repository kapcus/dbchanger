<?php

namespace Kapcus\DbChanger\Model;

use Kapcus\DbChanger\Entity\DbChange;
use Kapcus\DbChanger\Entity\Environment;
use Kapcus\DbChanger\Entity\Fragment;
use Kapcus\DbChanger\Entity\Requirement;
use Kapcus\DbChanger\Model\Exception\DbChangeException;
use Nette\Utils\Finder;

class Loader implements ILoader
{
	/**
	 * @var \Kapcus\DbChanger\Entity\DbChange[]
	 */
	private $dbChanges = [];

	/**
	 * @var string directory where all db changes are placed
	 */
	private $inputDirectory;

	/**
	 * @var string
	 */
	private $filenameMask = '*.sql';

	private $metaFileMask = '_requirements.txt';

	/**
	 * @var string
	 */
	private $filePrefix;


	public function __construct($inputDirectory, $filePrefix = '')
	{
		$this->inputDirectory = $inputDirectory;
		$this->filePrefix = $filePrefix;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Group[] $groups
	 *
	 * @return \Kapcus\DbChanger\Entity\DbChange[]
	 */
	public function loadDbChangesFromInputDirectory(array $groups) {
		foreach (Finder::findDirectories("*")->from($this->inputDirectory) as $dbChangeDirectory) {
			$dbChange = $this->loadDbChange($groups, $dbChangeDirectory);
			if ($dbChange !== null) {
				$this->addDbChange($dbChange);
			}
		}
		return $this->getDbChanges();
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Group[] $groups
	 * @param string $dbChangeCode
	 *
	 * @return \Kapcus\DbChanger\Entity\DbChange
	 * @throws \Kapcus\DbChanger\Model\Exception\DbChangeException
	 */
	public function loadDbChangeFromInputDirectory(array $groups, $dbChangeCode) {
		foreach (Finder::findDirectories("*")->from($this->inputDirectory) as $dbChangeDirectory) {
			if (strtoupper($dbChangeDirectory->getFilename()) == $dbChangeCode) {
				$dbChange =  $this->loadDbChange($groups, $dbChangeDirectory);
				if ($dbChange == null) {
					throw new DbChangeException(sprintf('DbChange %s is empty and therefore can not be initialized.', $dbChangeCode));
				}
				return $dbChange;
			}
		}
		throw new DbChangeException(sprintf('Input directory for DbChange %s not found.', $dbChangeCode));
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Group[] $groups
	 * @param string $dbChangeDirectory
	 *
	 * @return \Kapcus\DbChanger\Entity\DbChange|null
	 */
	private function loadDbChange(array $groups, $dbChangeDirectory)
	{
		$dbChange = null;
		foreach (Finder::findFiles(sprintf('%s%s', $this->filePrefix, $this->filenameMask))->in($dbChangeDirectory->getPathname()) as $file) {
			if (!isset($dbChange)) {
				$dbChangeCode = str_replace(['_', '.'], '', $dbChangeDirectory->getFilename());
				$dbChange = new DbChange();
				$dbChange->setCode(strtoupper($dbChangeCode));
				$this->loadRequiredDbChanges($dbChangeDirectory->getPathname(), $dbChange);
			}
			$filePart = substr($file->getFilename(), strlen($this->filePrefix));
			$fragmentIndex = intval(substr($filePart, 0, strpos($filePart, '_')));
			$startIndex = strrpos($file->getFilename(), '_')+1;
			$dbChangeGroupName = substr($file->getFilename(), $startIndex, strpos($file->getFilename(), '.sql')-$startIndex);
			if (($foundGroup = Util::getGroupByName($groups, $dbChangeGroupName)) !== null) {
				$fragment = new Fragment();
				$fragment->setGroup($foundGroup);
				$fragment->setDbChange($dbChange);
				$fragment->setFilename($file->getFilename());
				$fragment->setPathname($file->getPathname());
				$fragment->setIndex($fragmentIndex);
				$dbChange->addFragment($fragment);
			}
		}
		if ($dbChange instanceof DbChange && $dbChange->hasFragment()) {
			return $dbChange;
		}
		return null;
	}

	private function loadRequiredDbChanges($directoryPath, DbChange $dbChange) {
		if (!is_file($directoryPath.DIRECTORY_SEPARATOR.$this->metaFileMask)) {
			return $dbChange;
		}
		$lines = file($directoryPath.DIRECTORY_SEPARATOR.$this->metaFileMask);
		foreach($lines as $line) {
			$requiredDbChange = new DbChange();
			$requiredDbChange->setCode(strtoupper(trim($line)));
			$dbChange->addReqDbChanges($requiredDbChange);
		}
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\DbChange[]
	 */
	public function getDbChanges()
	{
		return $this->dbChanges;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\DbChange $dbChange
	 */
	public function addDbChange(\Kapcus\DbChanger\Entity\DbChange $dbChange)
	{
		$this->dbChanges[] = $dbChange;
	}

}