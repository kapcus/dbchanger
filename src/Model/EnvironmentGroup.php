<?php

namespace Kapcus\DbChanger\Model;

use Kapcus\DbChanger\Model\Exception\EnvironmentException;

class EnvironmentGroup
{

	/**
	 * @var \Kapcus\DbChanger\Model\User[]
	 */
	private $users = [];

	/**
	 * @var \Kapcus\DbChanger\Model\Group
	 */
	private $group;

	/**
	 * EnvironmentGroup constructor.
	 *
	 * @param $group
	 * @param array $users
	 *
	 * @throws \Kapcus\DbChanger\Model\Exception\EnvironmentException
	 */
	public function __construct($group, array $users)
	{
		$this->setGroup($group);
		$this->setUsers($users);
	}

	/**
	 * @return \Kapcus\DbChanger\Model\User[]
	 */
	public function getUsers()
	{
		return $this->users;
	}

	/**
	 * @param array $users
	 *
	 * @throws \Kapcus\DbChanger\Model\Exception\EnvironmentException
	 */
	private function setUsers(array $users)
	{
		foreach($users as $user) {
			if (!$user instanceof User) {
				throw new EnvironmentException('User must be instance of User object');
			}
			$this->users[] = $user;
		}
	}

	/**
	 * @return \Kapcus\DbChanger\Model\Group
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Group $group
	 */
	public function setGroup($group)
	{
		$this->group = $group;
	}





}