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
			return $this->connection->query('SELECT CODE FROM DBCH_DBCHANGE')->fetchAssoc('CODE');
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
			$this->connection->query('INSERT INTO DBCH_DBCHANGE (CODE, DESCRIPTION) VALUES (?, ?)', $dbChange->getCode(), $dbChange->getDescription());
			$id = $this->connection->getInsertId('DBCH_DBCH_SEQ');
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
			'INSERT INTO DBCH_FRAGMENT (DBCHANGE_ID, GROUP_ID, TEMPLATECONTENT) VALUES (?, ?, ?)',
			$fragment->getDbChange()->getId(),
			$fragment->getGroup()->getId(),
			$fragment->getTemplate()
		);
		$id = $this->connection->getInsertId('DBCH_FRAG_SEQ');
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
				$dbChange = $this->dbChangeFactory->createDbChange($result['CODE'], $this->getGroups());
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
				'INSERT INTO DBCH_ENVIRONMENT (CODE, NAME, DESCRIPTION, VERSION) VALUES (?, ?, ?, ?)',
				$environment->getCode(),
				$environment->getName(),
				$environment->getDescription(),
				0
			);
			$id = $this->connection->getInsertId('DBCH_ENV_SEQ');
			$environment->setId($id);

			return $environment;
		} catch (\Dibi\Exception $e) {
			throw new StorageException($e);
		}
	}


	public function createEnvironmentUserGroup(Environment $environment, UserGroup $userGroup)
	{
		try {
			$this->connection->query(
				'INSERT INTO DBCH_USERGROUP (ENVIRONMENT_ID, GROUP_ID, USER_ID) VALUES (?, ?, ?)',
				$environment->getId(),
				$userGroup->getGroup()->getId(),
				$userGroup->getUser()->getId()
			);
			$userGroup->setId($this->connection->getInsertId('DBCH_UG_SEQ'));
		} catch (\Dibi\Exception $e) {
			throw new StorageException($e);
		}
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
			$result = $this->connection->query('SELECT * FROM DBCH_ENVIRONMENT WHERE CODE = ?', $environmentCode)->fetch();
		} catch (\Dibi\Exception $e) {
			throw new StorageException($e);
		}
		if ($result !== false) {
			return new Environment($result['ID'], $result['CODE'], $result['NAME']);
		}

		return null;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\User $user
	 *
	 * @return \Kapcus\DbChanger\Model\User
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function storeUser(User $user)
	{
		try {
			$this->begin();
			$this->connection->query('INSERT INTO DBCH_USER (NAME) VALUES (?)', $user->getName());
			$id = $this->connection->getInsertId('DBCH_USER_SEQ');
			$user->setId($id);
			$this->commit();
		} catch (\Dibi\Exception $e) {
			$this->rollback();
			throw new StorageException($e);
		}

		return $user;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\User $user
	 *
	 * @return \Kapcus\DbChanger\Model\User
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function loadUserById($userId, User $user)
	{
		try {
			$result = $this->selectUserByName($user->getName());
			if ($result === false) {
				throw new StorageException();
			}
			$user->setId($result['ID']);
			return $user;
		} catch (\Dibi\Exception $e) {
			throw new StorageException($e);
		}
	}

	/**
	 * @param string $userName
	 *
	 * @return \Kapcus\DbChanger\Model\User|null
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function getUserByName($userName)
	{
		try {
			$result = $this->selectUserByName($userName);
		} catch (\Dibi\Exception $e) {
			throw new StorageException($e);
		}
		if ($result !== false) {
			return new User($result['ID'], $result['NAME']);
		}

		return null;
	}

	private function selectUserByName($userName) {
		return $this->connection->query('SELECT * FROM DBCH_USER WHERE NAME = ?', $userName)->fetch();
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Group $group
	 *
	 * @return \Kapcus\DbChanger\Model\Group
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function storeGroup(Group $group)
	{
		try {
			$this->begin();
			$this->connection->query('INSERT INTO DBCH_GROUP (NAME, IS_MANUAL) VALUES (?, ?)', $group->getName(), $group->isManual() ? 1 : 0);
			$id = $this->connection->getInsertId('DBCH_G_SEQ');
			$group->setId($id);
			$this->commit();
		} catch (\Dibi\Exception $e) {
			$this->rollback();
			throw new StorageException($e);
		}

		return $group;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Group $group
	 *
	 * @return \Kapcus\DbChanger\Model\Group
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function loadGroup(Group $group)
	{
		try {
			$result = $this->selectGroupByName($group->getName());
			if ($result === false) {
				throw new StorageException();
			}
			$group->setId($result['ID']);
			$group->setIsManual($result['IS_MANUAL']);
			return $group;
		} catch (\Dibi\Exception $e) {
			throw new StorageException($e);
		}
	}

	/**
	 * @param string $groupName
	 *
	 * @return \Kapcus\DbChanger\Model\Group|null
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function getGroupByName($groupName)
	{
		try {
			$result = $this->selectGroupByName($groupName);
		} catch (\Dibi\Exception $e) {
			throw new StorageException($e);
		}
		if ($result !== false) {
			return new Group($result['ID'], $result['NAME']);
		}

		return null;
	}

	private function selectGroupByName($groupName) {
		return $this->connection->query('SELECT * FROM DBCH_GROUP WHERE NAME = ?', $groupName)->fetch();
	}

	public function loadEnvironment(Environment $environment)
	{
		try {
			$result = $this->connection->query('SELECT * FROM DBCH_ENVIRONMENT WHERE CODE = ?', $environment->getCode())->fetch();
			if ($result === false) {
				throw new StorageException();
			}
			$environment->setId($result['ID']);
			$result = $this->connection->query(
				'SELECT
						UG.ID as ID, 
						G.ID as GROUPID, G.NAME as GNAME, G.IS_MANUAL as IS_MANUAL,
						U.ID as USERID, U.NAME as UNAME
					  FROM 
					  	DBCH_USERGROUP UG, DBCH_USER U, DBCH_GROUP G
					  WHERE 
					  	UG.ENVIRONMENT_ID = ? AND
					  	U.ID = UG.USER_ID AND 
					  	G.ID = UG.GROUP_ID',
				$environment->getId()
			)->fetchAll();
			foreach ($result as $data) {
				$user = $environment->getUserGroup($data['GNAME'], $data['UNAME']);
				if ($user !== null) {
					$user->setId($data['ID']);
				} else {
					throw new StorageException('Environment needs to be reinitialized to reflect current configuration.');
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
				'INSERT INTO DBCH_INSTALLATION (FRAGMENT_ID, USER_ID, DONE_AT, DONE_BY, CONTENT) VALUES (?, ?, CURRENT_TIMESTAMP, ?, ?)',
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

	/**
	 * @param \Kapcus\DbChanger\Model\DbChange $dbChange
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 *
	 * @return array
	 */
	public function getInstallationResults(DbChange $dbChange, Environment $environment)
	{
		try {
			$result = '';// = $this->connection->query('SELECT * FROM ENVIRONMENT WHERE CODE = ?', $environmentCode)->fetch();
		} catch (\Dibi\Exception $e) {
			throw new StorageException($e);
		}
		if ($result !== false) {
			return new Environment($result['ID'], $result['CODE'], $result['NAME']);
		}

		return null;
	}

	/*public function createEnvironmentGroup(Environment $environment, Group $group)
	{
		try {
			$this->connection->query(
				'INSERT INTO ENVIRONMENT_GROUP (ENVIRONMENT_ID, NAME, IS_MANUAL) VALUES (?, ?, ?)',
				$environment->getId(),
				$group->getName(),
				$group->isManual() ? 1 : 0
			);
			$group->setId($this->connection->getInsertId('ENVGROUP_SEQ'));
			foreach($environment->getUsersInGroup($group->getName()) as $user) {
				$this->connection->query(
					'INSERT INTO ENVIRONMENT_USERGROUP (USER_ID, GROUP_ID) VALUES (?, ?)',
					$user->getId(),
					$group->getId()
				);
			}
		} catch (\Dibi\Exception $e) {
			throw new StorageException($e);
		}
	}*/

	/**
	 * @return \Kapcus\DbChanger\Model\Group[]
	 * @throws \Kapcus\DbChanger\Model\Exception\StorageException
	 */
	public function getGroups() {
		try {
			$result = $this->connection->query(
				'SELECT
							G.ID as ID, G.NAME as NAME, G.IS_MANUAL as IS_MANUAL
						  FROM 
							DBCH_GROUP G'
			)->fetchAll();
		} catch (\Dibi\Exception $e) {
			throw new StorageException($e);
		}
		$groups = [];
		foreach ($result as $data) {
			$groups[] = new Group($data['ID'], $data['NAME'], $data['IS_MANUAL']);
		}
		return $groups;
	}
}