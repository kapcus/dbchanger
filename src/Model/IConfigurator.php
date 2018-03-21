<?php

namespace Kapcus\DbChanger\Model;

interface IConfigurator
{
	/**
	 * @return string[]
	 */
	function getEnvironments();

	/**
	 * @param string $code
	 *
	 * @return \Kapcus\DbChanger\Model\Environment
	 */
	function getEnvironmentByCode($code);

	/**
	 * @return \Kapcus\DbChanger\Model\Group[]
	 */
	function getGroups();

	/**
	 * @return \Kapcus\DbChanger\Model\User[]
	 */
	function getUsers();

	/**
	 * @return string[]
	 */
	function getGroupNames();

	/**
	 * @param string $groupName
	 *
	 * @return \Kapcus\DbChanger\Model\Group
	 */
	function getGroupByName($groupName);
}