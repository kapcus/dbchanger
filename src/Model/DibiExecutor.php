<?php

namespace Kapcus\DbChanger\Model;

use Dibi\Connection;
use Dibi\Exception;
use Doctrine\Common\Util\Debug;
use Kapcus\DbChanger\Model\Exception\ConnectionException;
use Kapcus\DbChanger\Model\Exception\ExecutionException;

class DibiExecutor implements IExecutor
{
	/**
	 * @var \Kapcus\DbChanger\Model\IDatabase
	 */
	private $database;

	/**
	 * @var bool
	 */
	private $isDebug = true;

	/**
	 * @var \Dibi\Connection
	 */
	private $connection;

	/**
	 * @var \Kapcus\DbChanger\Model\IParser
	 */
	private $parser;

	/**
	 * @var string
	 */
	private $logDirectory;

	public function __construct($logDirectory, IDatabase $database, IParser $parser)
	{
		$this->database = $database;
		$this->logDirectory = $logDirectory;
		$this->parser = $parser;

		if ($this->isDebug) {
			if (!is_dir($this->logDirectory)) {
				mkdir($this->logDirectory);
			}
		}
	}

	/**
	 * @param \Kapcus\DbChanger\Model\ConnectionConfiguration $connectionConfiguration
	 *
	 * @throws \Kapcus\DbChanger\Model\Exception\ConnectionException
	 */
	public function setupConnection(ConnectionConfiguration $connectionConfiguration)
	{
		$this->writeLog(sprintf('---------------'));
		$this->writeLog(date('d.m.Y H:i:s'));
		$this->writeLog(sprintf('---------------'));
		try {
			$this->setConnection(new Connection($this->database->getConnectionOptions($connectionConfiguration)));
		} catch (Exception $e) {
			$this->writeLog(sprintf('Unable to connect.'));
			throw new ConnectionException('Unable to connect.', 0, $e);
		}
		$this->writeLog(sprintf('Connected as user %1$s (host %2$s).', $connectionConfiguration->getUsername(), $connectionConfiguration->getHostname()));
	}

	/**
	 * @param string $sqlContent
	 *
	 * @throws \Kapcus\DbChanger\Model\Exception\ExecutionException
	 */
	public function executeContent($sqlContent)
	{
		$statements = $this->parser->parseContent($sqlContent);
		foreach($statements as $statement) {
			$this->runQuery($statement->getContent());
		}
		//$this->parser->applyOnEachStatement($sqlContent, [$this, 'runQuery']);
	}

	/**
	 * @param string $sqlQuery
	 *
	 * @throws \Kapcus\DbChanger\Model\Exception\ExecutionException
	 */
	private function runQuery($sqlQuery)
	{
		try {
			$start = new \DateTime();
			$this->getConnection()->query($sqlQuery);
			$end = new \DateTime();

			if ($this->isDebug) {
				$this->writeLog(sprintf('OK (%s) : %s', $start->diff($end)->format("%H:%I:%S"), $sqlQuery));
			}
		} catch (Exception $e) {
			$this->writeLog(sprintf('FAILED : %s', $sqlQuery));
			throw new ExecutionException(sprintf('Unable to execute following query: %s', $sqlQuery), 0, $e);
		}
	}

	/**
	 * @param $message
	 */
	private function writeLog($message) {
		file_put_contents($this->logDirectory . DIRECTORY_SEPARATOR . 'executor.log', $message . PHP_EOL, FILE_APPEND);
	}

	/**
	 * @return \Dibi\Connection
	 */
	public function getConnection()
	{
		return $this->connection;
	}

	/**
	 * @param \Dibi\Connection $connection
	 */
	public function setConnection($connection)
	{
		$this->connection = $connection;
	}


}