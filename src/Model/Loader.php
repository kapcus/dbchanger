<?php

namespace Kapcus\DbChanger\Model;

use Kapcus\DbChanger\Model\Exception\DbChangeException;
use Nette\Utils\Finder;

class Loader implements ILoader
{
	/**
	 * @var \Kapcus\DbChanger\Model\DbChange[]
	 */
	private $dbChanges = [];

	/**
	 * @var string directory where all db changes are placed
	 */
	private $inputDirectory;
	private $filenameMask = 'dbchange_*.sql';

	/**
	 * @var \Kapcus\DbChanger\Model\IDescriptor
	 */
	private $descriptor;

	public function __construct($inputDirectory, IDescriptor $descriptor)
	{
		$this->descriptor = $descriptor;
		$this->inputDirectory = $inputDirectory;
	}

	/*public function loadDbChanges() {
		foreach (Finder::findDirectories("*")->from($this->inputDirectory) as $dbChangeDirectory) {
			$this->loadDbChange($dbChangeDirectory);
		}
		return $this->getDbChanges();
	}

	private function loadDbChange($dbChangeDirectory)
	{
		$dbChange = null;
		foreach (Finder::findFiles($this->filenameMask)->in($dbChangeDirectory->getPathname()) as $file) {
			if (!isset($dbChange)) {
				$dbChangeCode = str_replace(['_', '.'], '', $dbChangeDirectory->getFilename());
				$dbChange = new DbChange(null, strtoupper($dbChangeCode));
			}

			$startIndex = strrpos($file->getFilename(), '_')+1;
			$dbChangeGroup = substr($file->getFilename(), $startIndex, strpos($file->getFilename(), '.sql')-$startIndex);
			if (($foundGroup = $this->descriptor->getGroupByName($dbChangeGroup)) !== null) {
				$dbChange->addFragment(new DbChangeFragment($foundGroup, $file->getPathname(), $file->getFilename()));
			}
		}
		if ($dbChange instanceof DbChange && $dbChange->hasFragment()) {
			$this->addDbChange($dbChange);
		}
	}*/

	public function loadExistingDbChange(DbChange $dbChange) {
		$dbChangeDirectory = $this->inputDirectory . DIRECTORY_SEPARATOR . $dbChange->getCode();

		if (!is_dir($dbChangeDirectory)) {
			throw new DbChangeException(sprintf('DbChange %s can\'t be loaded as %s directory not found.', $dbChange->getCode(), $dbChangeDirectory));
		}
		foreach (Finder::findFiles($this->filenameMask)->in($dbChangeDirectory) as $file) {
			$startIndex = strrpos($file->getFilename(), '_')+1;
			$dbChangeGroup = substr($file->getFilename(), $startIndex, strpos($file->getFilename(), '.sql')-$startIndex);
			if (($foundGroup = $this->descriptor->getGroupByName($dbChangeGroup)) !== null) {
				$fragment = new Fragment(null, $dbChange, $foundGroup);
				$fragment->setFilePath($file->getPathname());
				$fragment->setFilename($file->getFilename());
				$dbChange->addFragment($fragment);
			}
		}
		return $dbChange;
	}

	/**
	 * @return \Kapcus\DbChanger\Model\DbChange[]
	 */
	public function getDbChanges()
	{
		return $this->dbChanges;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\DbChange $dbChange
	 */
	public function addDbChange(DbChange $dbChange)
	{
		$this->dbChanges[] = $dbChange;
	}

}