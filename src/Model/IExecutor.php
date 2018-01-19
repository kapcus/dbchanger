<?php

namespace Kapcus\DbChanger\Model;

interface IExecutor
{
	/**
	 * @param string $filenamePath
	 *
	 * @return int number of executed queries
	 * @throw ExecutorException
	 */
	public function loadFile($filenamePath);

	/**
	 * @param string $sqlContent
	 *
	 * @return int number of executed queries
	 * @throw ExecutorException
	 */
	public function loadContent($sqlContent);

	/**
	 * @param string $sqlQuery
	 *
	 * @return boolean
	 * @throw ExecutorException
	 */
	public function loadQuery($sqlQuery);

	public function begin();

	public function commit();

	public function rollback();
}