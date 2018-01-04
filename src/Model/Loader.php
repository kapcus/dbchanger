<?php

namespace Kapcus\DbChanger\Model;

use Nette\Utils\Finder;

class Loader implements ILoader
{
	/**
	 * @var \Kapcus\DbChanger\Model\DbChange[]
	 */
	private $dbChanges = [];
	private $homeDirectory = 'c:\workgit\FSI\datamodel\db-changes\data\3.10.3';
	private $filenameMask = 'dbchange_*.sql';

	/**
	 * @var \Kapcus\DbChanger\Model\IDescriptor
	 */
	private $descriptor;

	public function __construct(IDescriptor $descriptor)
	{
		$this->descriptor = $descriptor;
	}

	public function loadDbChanges() {
		foreach (Finder::findDirectories("*")->from($this->homeDirectory) as $dbChangeDirectory) {
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
				$dbChange = new DbChange($dbChangeCode);
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