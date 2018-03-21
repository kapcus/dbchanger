<?php

namespace Kapcus\DbChanger\Model;

use Kapcus\DbChanger\Entity\Environment;
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
	private $filenameMask = 'dbchange_*.sql';

	public function __construct($inputDirectory)
	{
		$this->inputDirectory = $inputDirectory;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 *
	 * @return \Kapcus\DbChanger\Entity\DbChange[]
	 */
	public function loadDbChangesFromInputDirectory(Environment $environment) {
		foreach (Finder::findDirectories("*")->from($this->inputDirectory) as $dbChangeDirectory) {
			$this->loadDbChange($environment, $dbChangeDirectory);
		}
		return $this->getDbChanges();
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param string $dbChangeDirectory
	 */
	private function loadDbChange($environment, $dbChangeDirectory)
	{
		$groups = Util::getGroupsFromUserGroups($environment->getUserGroups());
		foreach (Finder::findFiles($this->filenameMask)->in($dbChangeDirectory->getPathname()) as $file) {
			if (!isset($dbChange)) {
				$dbChangeCode = str_replace(['_', '.'], '', $dbChangeDirectory->getFilename());
				$dbChange = new \Kapcus\DbChanger\Entity\DbChange();
				$dbChange->setCode(strtoupper($dbChangeCode));
			}

			$startIndex = strrpos($file->getFilename(), '_')+1;
			$dbChangeGroupName = substr($file->getFilename(), $startIndex, strpos($file->getFilename(), '.sql')-$startIndex);
			if (($foundGroup = Util::getGroupByName($groups, $dbChangeGroupName)) !== null) {
				$fragment = new \Kapcus\DbChanger\Entity\Fragment();
				$fragment->setGroup($foundGroup);
				$fragment->setDbChange($dbChange);
				$fragment->setFilename($file->getFilename());
				$fragment->setPathname($file->getPathname());
				$dbChange->addFragment($fragment);
			}
		}
		if ($dbChange instanceof \Kapcus\DbChanger\Entity\DbChange && $dbChange->hasFragment()) {
			$this->addDbChange($dbChange);
		}
	}

	/*public function loadExistingDbChange(DbChange $dbChange, array $groups) {
		$dbChangeDirectory = $this->inputDirectory . DIRECTORY_SEPARATOR . $dbChange->getCode();

		if (!is_dir($dbChangeDirectory)) {
			throw new DbChangeException(sprintf('DbChange %s can\'t be loaded as %s directory not found.', $dbChange->getCode(), $dbChangeDirectory));
		}
		foreach (Finder::findFiles($this->filenameMask)->in($dbChangeDirectory) as $file) {
			$startIndex = strrpos($file->getFilename(), '_')+1;
			$dbChangeGroupName = substr($file->getFilename(), $startIndex, strpos($file->getFilename(), '.sql')-$startIndex);
			if (($foundGroup = Util::getGroupByName($groups, $dbChangeGroupName)) !== null) {
				$fragment = new Fragment(null, $dbChange, $foundGroup);
				$fragment->setFilePath($file->getPathname());
				$fragment->setFilename($file->getFilename());
				$dbChange->addFragment($fragment);
			}
		}
		return $dbChange;
	}*/

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