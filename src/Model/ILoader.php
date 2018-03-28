<?php

namespace Kapcus\DbChanger\Model;

interface ILoader
{
	/**
	 * @param \Kapcus\DbChanger\Entity\Group[] $groups
	 *
	 * @return \Kapcus\DbChanger\Entity\DbChange[]
	 */
	function loadDbChangesFromInputDirectory(array $groups);

	/**
	 * @param \Kapcus\DbChanger\Entity\Group[] $groups
	 * @param string $dbChangeCode
	 *
	 * @return \Kapcus\DbChanger\Entity\DbChange
	 */
	function loadDbChangeFromInputDirectory(array $groups, $dbChangeCode);

}