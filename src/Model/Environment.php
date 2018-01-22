<?php

namespace Kapcus\DbChanger\Model;

class Environment
{
	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $code;

	/**
	 * @var string
	 */
	private $description;

	/**
	 * @var \Kapcus\DbChanger\Model\EnvironmentGroup[]
	 */
	private $groups = [];

	/**
	 * @var \Kapcus\DbChanger\Model\User[]
	 */
	private $users = [];

	/**
	 * @var \Kapcus\DbChanger\Model\Placeholder[]
	 */
	private $placeholders = [];

	public function __construct($id, $code, $name)
	{
		if ($id !== null) {
			$this->setId($id);
		}
		$this->setCode($code);
		$this->setName($name);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param string $code
	 */
	public function setCode($code)
	{
		$this->code = $code;
	}

	/**
	 * @return \Kapcus\DbChanger\Model\Placeholder[]
	 */
	public function getPlaceholders()
	{
		return $this->placeholders;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Placeholder $placeholder
	 */
	public function addPlaceholder(Placeholder $placeholder)
	{
		$this->placeholders[] = $placeholder;
	}

	public function getPlaceholderCodes()
	{
		return array_map(
			function (Placeholder $o) {
				return $o->getCode();
			},
			$this->getPlaceholders()
		);
	}

	public function getPlaceholderValues()
	{
		return array_map(
			function (Placeholder $o) {
				return $o->getValue();
			},
			$this->getPlaceholders()
		);
	}

	/**
	 * @return \Kapcus\DbChanger\Model\EnvironmentGroup[]
	 */
	public function getGroups()
	{
		return $this->groups;
	}

	/**
	 * @param $groupName
	 *
	 * @return \Kapcus\DbChanger\Model\User[]|null
	 */
	public function getUsersInGroup($groupName) {
		foreach($this->groups as $group) {
			if ($group->getGroup()->getName() == $groupName) {
				return $group->getUsers();
			}
		}
		return [];
	}

	/**
	 * @param \Kapcus\DbChanger\Model\EnvironmentGroup $group
	 */
	public function addGroup(EnvironmentGroup $group)
	{
		$this->groups[] = $group;
		foreach ($group->getUsers() as $user) {
			if (!array_key_exists($user->getName(), $this->users)) {
				$this->addUser($user);
				break;
			}
		}
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return bool
	 */
	public function isPersistent() {
		return $this->getId() !== null;
	}

	public function isFullyPersistent() {
		if (!$this->isPersistent()) {
			return false;
		}
		foreach ($this->getUsers() as $user) {
			if (!$user->isPersistent()) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @return \Kapcus\DbChanger\Model\User[]
	 */
	public function getUsers()
	{
		return $this->users;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\User $user
	 */
	public function addUser(User $user)
	{
		$this->users[$user->getName()] = $user;
	}

	/**
	 * @param string $userName
	 *
	 * @return \Kapcus\DbChanger\Model\User|null
	 */
	public function getUserByName($userName) {
		foreach($this->getUsers() as $user) {
			if ($user->getName() == $userName) {
				return $user;
			}
		}
		return null;
	}


}