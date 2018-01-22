<?php

namespace Kapcus\DbChanger\Model;

use Kapcus\DbChanger\Model\Exception\ExecutorException;
use Kapcus\DbChanger\Model\Exception\StorageException;

class DibiExecutor implements IExecutor
{

	/**
	 * @var \Kapcus\DbChanger\Model\IDescriptor
	 */
	private $descriptor;

	/**
	 * @var \Kapcus\DbChanger\Model\DibiStorage
	 */
	private $storage;

	/**
	 * @var bool
	 */
	private $isDebug = true;

	/**
	 * @var string
	 */
	private $logDirectory;

	public function __construct($logDirectory, IDescriptor $descriptor, DibiStorage $storage)
	{
		$this->descriptor = $descriptor;
		$this->storage = $storage;
		$this->logDirectory = $logDirectory;

		if ($this->isDebug) {
			if (!is_dir($this->logDirectory)) {
				mkdir($this->logDirectory);
			}
		}
	}

	public function installDbChange(Environment $environment, $dbChangeCode) {

		//$this->storage->commitInstalledDbChange($storedEnvironment, $registeredDbChange);
	}


	/**
	 * @param string $filenamePath
	 *
	 * @return int number of executed queries
	 * @throw ExecutorException
	 */
	public function loadFile($filenamePath)
	{
		var_dump($filenamePath);
	}

	/**
	 * @param string $sqlContent
	 *
	 * @return int number of executed queries
	 * @throw ExecutorException
	 */
	public function loadContent($sqlContent)
	{
		$lines = preg_split ('/$\R?^/m', $sqlContent);
		$sqlQuery = '';
		foreach($lines as $line) {
			if (strpos(ltrim($line),'--') === 0 || strpos(ltrim($line),'//') === 0) {
				continue;
			}
			$sqlQuery .= $line;
			if (substr(rtrim($sqlQuery), -1) === ';') {
				$this->runQuery(rtrim(rtrim($sqlQuery), ';'));
				$sqlQuery = '';
			}
		}
		// missing delimiter at the end of the script file
		if (trim($sqlQuery) !== '') {
			$this->runQuery($sqlQuery);
		}
	}

	private function runQuery($sqlQuery) {
		try {
			$start = microtime(true);
			$this->storage->query($sqlQuery);
			$duration = microtime(true) - $start;
			if ($this->isDebug) {
				file_put_contents($this->logDirectory . DIRECTORY_SEPARATOR . 'executor.log', sprintf('OK (%s) : %s', $duration, $sqlQuery).PHP_EOL, FILE_APPEND);
			}
		} catch (StorageException $e) {
			file_put_contents($this->logDirectory . DIRECTORY_SEPARATOR . 'executor.log', sprintf('FAILED : %s', $sqlQuery).PHP_EOL, FILE_APPEND);
			throw new ExecutorException(sprintf('Unable to execute following query: %s', $sqlQuery), 0, $e);

		}
	}

	/**
	 * @param string $sqlQuery
	 *
	 * @return boolean
	 * @throw ExecutorException
	 */
	public function loadQuery($sqlQuery)
	{
		// TODO: Implement loadQuery() method.
	}

	public function begin()
	{
		$this->storage->begin();
	}

	public function commit()
	{
		$this->storage->commit();
	}

	public function rollback()
	{
		$this->storage->rollback();
	}
}