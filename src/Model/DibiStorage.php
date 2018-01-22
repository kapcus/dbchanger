<?php

namespace Kapcus\DbChanger\Model;

use Dibi\Connection;
use Dibi\Exception;
use Kapcus\DbChanger\Model\Exception\DbChangeException;
use Kapcus\DbChanger\Model\Exception\StorageException;

class DibiStorage implements IStorage
{
	/**
	 * @var \Kapcus\DbChanger\Model\IDatabase
	 */
	private $database;

	/**
	 * @var \Dibi\Connection
	 */
	private $connection;

	/**
	 * @var \Kapcus\DbChanger\Model\DbChangeFactory
	 */
	private $dbChangeFactory;

	private $username;

	public function __construct(array $options, IDatabase $database, DbChangeFactory $dbChangeFactory)
	{
		$this->connection = new Connection($options);
		$this->username = $options['username'];
		$this->database = $database;
		$this->dbChangeFactory = $dbChangeFactory;
	}

	/**
	 * @throws \Dibi\Exception
	 */
	private function resetUser()
	{
		$this->connection->query($this->database->getChangeUserSql($this->username));
	}

	/**
	 * @param string $sqlQuery
	 *
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function query($sqlQuery)
	{
		try {
			$this->connection->query($sqlQuery);
		} catch (Exception $e) {
			throw new StorageException($e);
		}
	}

	/**
	 * @return string[]
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	function getDbChangeCodes()
	{
		try {
			return $this->connection->query('SELECT CODE FROM DBCHANGE')->fetchAssoc('CODE');
		} catch (\Dibi\Exception $e) {
			throw new StorageException($e);
		}
	}

	/**
	 * @param \Kapcus\DbChanger\Model\DbChange $dbChange
	 *
	 * @return \Kapcus\DbChanger\Model\DbChange
	 * @throws \Kapcus\DbChanger\Model\Exception\DbChangeException
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function storeDbChange(DbChange $dbChange)
	{
		try {
			$this->begin();
			$this->connection->query('INSERT INTO DBCHANGE (CODE, DESCRIPTION) VALUES (?, ?)', $dbChange->getCode(), $dbChange->getDescription());
			$id = $this->connection->getInsertId('DBCHANGE_SEQ');
			$dbChange->setId($id);
			foreach ($dbChange->getFragments() as $fragment) {
				$this->storeFragment($fragment);
			}
			$this->commit();
		} catch (\Dibi\Exception $e) {
			$this->rollback();
			throw new StorageException($e);
		}

		return $dbChange;
	}

	private function storeFragment(Fragment $fragment)
	{
		$this->connection->query(
			'INSERT INTO FRAGMENT (DBCHANGE_ID, GROUPNAME, IS_MANUAL, TEMPLATECONTENT) VALUES (?, ?, ?, ?)',
			$fragment->getDbChange()->getId(),
			$fragment->getGroup()->getName(),
			$fragment->getGroup()->isManual() ? 1 : 0,
			$fragment->getTemplate()
		);
		$id = $this->connection->getInsertId('FRAGMENT_SEQ');
		$fragment->setId($id);
	}

	/**
	 * @param string $dbChangeCode
	 *
	 * @return \Kapcus\DbChanger\Model\DbChange|null
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function getDbChangeByCode($dbChangeCode)
	{
		try {
			$result = $this->connection->query('SELECT * FROM DBCHANGE WHERE CODE = ?', $dbChangeCode)->fetch();
			if ($result !== false) {
				$dbChange = $this->dbChangeFactory->createDbChange($result['CODE']);
				$dbChange->setId($result['ID']);
				$fragmentResult = $this->connection->query('SELECT * FROM FRAGMENT WHERE DBCHANGE_ID = ? ORDER BY ID ASC', $result['ID'])->fetchAll();
				foreach ($fragmentResult as $row) {
					$fragment = $dbChange->getFragmentByGroupName($row['GROUPNAME']);
					if ($fragment !== null) {
						$fragment->setId($row['ID']);
					} else {
						//TODO out of sync
					}
				}

				return $dbChange;
			}

			return null;
		} catch (\Dibi\Exception $e) {
			throw new StorageException($e);
		}
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 *
	 * @return \Kapcus\DbChanger\Model\Environment|null
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function storeEnvironment(Environment $environment)
	{
		try {
			$this->connection->query(
				'INSERT INTO ENVIRONMENT (CODE, NAME, DESCRIPTION, VERSION) VALUES (?, ?, ?, ?)',
				$environment->getCode(),
				$environment->getName(),
				$environment->getDescription(),
				0
			);
			$id = $this->connection->getInsertId('ENVIRONMENT_SEQ');
			$environment->setId($id);

			return $environment;
		} catch (\Dibi\Exception $e) {
			throw new StorageException($e);
		}
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 * @param string $userName
	 *
	 * @return mixed
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function createEnvironmentUser(Environment $environment, $userName)
	{
		try {
			$this->connection->query(
				'INSERT INTO ENVIRONMENT_USER (ENVIRONMENT_ID, NAME) VALUES (?, ?)',
				$environment->getId(),
				$userName
			);

			$id = $this->connection->getInsertId('ENVUSER_SEQ');

			//TODO replace this call with getLastInsertId and return persistent obj
			return $this->getEnvironmentUserByName($environment, $userName);
		} catch (\Dibi\Exception $e) {
			throw new StorageException($e);
		}
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 * @param string $userName
	 *
	 * @return mixed
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function getEnvironmentUserByName(Environment $environment, $userName)
	{
		try {
			$result = $this->connection->query(
				'SELECT ID, NAME, ENVIRONMENT_ID FROM ENVIRONMENT_USER WHERE ENVIRONMENT_ID = ? AND NAME = ?',
				$environment->getId(),
				$userName
			)->fetch();
		} catch (\Dibi\Exception $e) {
			throw new StorageException($e);
		}
		if ($result !== false) {
			return new User($result['ID'], $environment, $result['NAME']);
		}

		return null;
	}

	/**
	 * @param string $environmentCode
	 *
	 * @return \Kapcus\DbChanger\Model\Environment|null
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function getEnvironmentByCode($environmentCode)
	{
		try {
			$result = $this->connection->query('SELECT * FROM ENVIRONMENT WHERE CODE = ?', $environmentCode)->fetch();
		} catch (\Dibi\Exception $e) {
			throw new StorageException($e);
		}
		if ($result !== false) {
			return new Environment($result['ID'], $result['CODE'], $result['NAME']);
		}

		return null;
	}

	public function loadEnvironment(Environment $environment)
	{
		try {
			$result = $this->connection->query('SELECT * FROM ENVIRONMENT WHERE CODE = ?', $environment->getCode())->fetch();
			if ($result === false) {
				return;
			}
			$environment->setId($result['ID']);
			$result = $this->connection->query(
				'SELECT ID, NAME FROM ENVIRONMENT_USER WHERE ENVIRONMENT_ID = ?',
				$environment->getId()
			)->fetchAssoc('NAME');
			foreach ($result as $userName => $data) {
				$user = $environment->getUserByName($userName);
				if ($user !== null) {
					$user->setId($data['ID']);
				} else {
					//TODO out of sync
				}
			}
		} catch (\Dibi\Exception $e) {
			throw new StorageException($e);
		}
	}

	/**
	 * @param int $environmentId
	 * @param int $dbChangeId
	 *
	 * @return boolean
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function isDbChangeInstalled($environmentId, $dbChangeId)
	{
		try {
			$result = $this->connection->query(
				'SELECT ID FROM INSTALLATION WHERE USER_ID IN 
						(SELECT ID FROM ENVIRONMENT_USER WHERE ENVIRONMENT_ID = ?)
						AND FRAGMENT_ID IN (SELECT ID FROM FRAGMENT WHERE DBCHANGE_ID = ?)',
				$environmentId,
				$dbChangeId
			)->fetch();

			return ($result !== false);
		} catch (\Dibi\Exception $e) {
			throw new StorageException($e);
		}
		/*if ($result !== false) {
			return $this->dbChangeFactory->createPersistentDbChange($result['DBCHANGE_ID'], $result['CODE']);
			//sprintf('DbChange %s is already installed in environment %s.', $dbChange->getCode(), $environment->getCode())
		}
		return null;*/
	}

	/**
	 * @throws \Dibi\Exception
	 */
	public function begin()
	{
		$this->connection->begin();
	}

	public function commit()
	{
		$this->connection->commit();
	}

	public function rollback()
	{
		$this->connection->rollback();
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Fragment $fragment
	 * @param \Kapcus\DbChanger\Model\User $user
	 *
	 * @return boolean
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function confirmFragmentIsInstalled(Fragment $fragment, User $user)
	{
		if (!$fragment->isPersistent()) {
			throw new StorageException('Fragment must be persistent.');
		}
		try {
			$this->resetUser();
			$result = $this->connection->query(
				'INSERT INTO INSTALLATION (FRAGMENT_ID, USER_ID, DONE_AT, DONE_BY, CONTENT) VALUES (?, ?, CURRENT_TIMESTAMP, ?, ?)',
				$fragment->getId(),
				$fragment->getUserByName($user->getName())->getId(),
				0,
				$fragment->getUserContent($user)
			);

			return ($result !== false);
		} catch (\Dibi\Exception $e) {
			throw new StorageException($e);
		}
	}
}