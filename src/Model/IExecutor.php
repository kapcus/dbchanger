<?php

namespace Kapcus\DbChanger\Model;

interface IExecutor
{
	/**
	 * @param \Kapcus\DbChanger\Model\ConnectionConfiguration $connectionConfiguration
	 *
	 * @throws \Kapcus\DbChanger\Model\Exception\ConnectionException
	 */
	public function setupConnection(ConnectionConfiguration $connectionConfiguration);

	/**
	 * @param string $sqlContent
	 *
	 * @throws \Kapcus\DbChanger\Model\Exception\ExecutionException
	 */
	public function executeContent($sqlContent);

}