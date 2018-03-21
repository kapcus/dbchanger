<?php

namespace Kapcus\DbChanger\Model;

use Kapcus\DbChanger\Entity\Environment;
use Kapcus\DbChanger\Entity\Group;

interface ILoader
{
	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 *
	 * @return \Kapcus\DbChanger\Model\DbChange[]
	 */
	function loadDbChangesFromInputDirectory(Environment $environment);

	/**
	 * @param \Kapcus\DbChanger\Model\DbChange $dbChange
	 * @param \Kapcus\DbChanger\Model\Group[] $groups
	 *
	 * @return mixed
	 */
	//function loadExistingDbChange(DbChange $dbChange, array $groups);
}