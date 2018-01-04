<?php

namespace Kapcus\DbChanger\Model;

interface IDescriptor
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