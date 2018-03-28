<?php

namespace Kapcus\DbChanger\Model;

interface IDatabase
{

	/**
	 * @param string $user
	 *
	 * @return string
	 */
	function getChangeUserSql($user);

	/**
	 * @param \Kapcus\DbChanger\Model\ConnectionConfiguration $connectionConfiguration
	 *
	 * @return string[]
	 */
	function getConnectionOptions(ConnectionConfiguration $connectionConfiguration);
}