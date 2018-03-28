<?php

namespace Kapcus\DbChanger\Model;

interface IConfigurator
{
	/**
	 * @return string[]
	 */
	function getEnvironments();

	/**
	 * @return \Kapcus\DbChanger\Entity\Group[]
	 */
	function getGroups();

	/**
	 * @return string[]
	 */
	function getGroupNames();

	/**
	 * @param string $environmentCode
	 *
	 * @return \Kapcus\DbChanger\Model\ConnectionConfiguration[]
	 */
	function getEnvironmentConnectionConfigurations($environmentCode);
}