<?php

namespace Kapcus\DbChanger\Model\Database;

use Kapcus\DbChanger\Model\ConnectionConfiguration;
use Kapcus\DbChanger\Model\IDatabase;

class Oracle implements IDatabase
{
	/**
	 * @param string $user
	 *
	 * @return string
	 */
	function getChangeUserSql($user)
	{
		return sprintf('ALTER SESSION SET CURRENT_SCHEMA = %1$s', $user);
	}

	/**
	 * @param \Kapcus\DbChanger\Model\ConnectionConfiguration $connectionConfiguration
	 *
	 * @return string[]
	 */
	function getConnectionOptions(ConnectionConfiguration $connectionConfiguration)
	{
		return [
			'driver' => 'oracle',
			'username' => $connectionConfiguration->getUsername(),
			'password' => $connectionConfiguration->getPassword(),
			'database' => sprintf('%1$s:%2$s/%3$s', $connectionConfiguration->getHostname(), $connectionConfiguration->getPort(), $connectionConfiguration->getDatabaseName())
		];
	}
}