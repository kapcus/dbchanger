<?php

namespace Kapcus\DbChanger\Model;

interface ILoader
{
	/**
	 * @return \Kapcus\DbChanger\Model\DbChange[]
	 */
	//function loadDbChanges();

	/**
	 * @param \Kapcus\DbChanger\Model\DbChange $dbChange
	 *
	 * @return \Kapcus\DbChanger\Model\DbChange
	 * @throws \Kapcus\DbChanger\Model\Exception\DbChangeException
	 */
	function loadExistingDbChange(DbChange $dbChange);
}