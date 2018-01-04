<?php

namespace Kapcus\DbChanger\Model;

class Descriptor implements IDescriptor
{
	/**
	 * @var \Kapcus\DbChanger\Model\Environment[]
	 */
	private $environments = [];

	/**
	 * @var \Kapcus\DbChanger\Model\Group[]
	 */
	private $groups = [];

	/**
	 * @var string[]
	 */
	private $groupNames = [];

	/**
	 * @var \Kapcus\DbChanger\Model\User[]
	 */
	private $users = [];

	public function __construct($groups, $environments)
	{
		$this->configureGroups($groups);
		$this->configureEnvironments($environments);
	}

	/**
	 * @param string $code
	 *
	 * @return \Kapcus\DbChanger\Model\Environment
	 */
	function getEnvironmentByCode($code)
	{
		foreach ($this->environments as $environment) {
			if ($environment->getCode() === $code) {
				return $environment;
			}
		}

		return null;
	}

	/**
	 * @return \Kapcus\DbChanger\Model\Environment[]
	 */
	public function getEnvironments()
	{
		return $this->environments;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 */
	public function addEnvironment(Environment $environment)
	{
		$this->environments[] = $environment;
	}

	/**
	 * @return \Kapcus\DbChanger\Model\Group[]
	 */
	public function getGroups()
	{
		return $this->groups;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Group $group
	 */
	public function addGroup(Group $group)
	{
		$this->groups[] = $group;
		$this->addGroupName($group->getName());
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
	public function addUsers(User $user)
	{
		$this->users[] = $user;
	}

	/**
	 * @return string[]
	 */
	public function getGroupNames()
	{
		return $this->groupNames;
	}

	/**
	 * @param string $groupName
	 */
	public function addGroupName($groupName)
	{
		$this->groupNames[] = $groupName;
	}

	public function getGroupByName($groupName)
	{
		foreach ($this->getGroups() as $group) {
			if ($group->getName() == $groupName) {
				return $group;
			}
		}

		return null;
	}

	public function getUserByName($userName)
	{
		foreach ($this->getUsers() as $user) {
			if ($user->getName() == $userName) {
				return $user;
			}
		}

		return null;
	}

	private function configureGroups($groups)
	{
		foreach ($groups as $group) {
			$this->addGroup(new Group($group));
		}
	}

	/**
	 * @param array $environments
	 */
	private function configureEnvironments(array $environments)
	{
		foreach ($environments as $envCode => $envData) {
			$env = new Environment($envCode, $envData['name']);
			foreach ($envData['placeholders'] as $placeholder => $value) {
				$env->addPlaceholder(new Placeholder($placeholder, $value));
			}

			foreach ($envData['groups'] as $groupName => $userNames) {
				$envUsers = [];
				$group = $this->getGroupByName($groupName);
				foreach ($userNames as $userName) {
					if (($user = $this->getUserByName($userName)) === null) {
						$user = new User($userName);
						$this->addUsers($user);
					}
					$envUsers[] = $user;
				}
				$env->addGroup(new EnvironmentGroup($group, $envUsers));
			}
			$this->addEnvironment($env);
		}
	}
}