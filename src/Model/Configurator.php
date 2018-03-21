<?php

namespace Kapcus\DbChanger\Model;

use Kapcus\DbChanger\Entity\Environment;
use Kapcus\DbChanger\Entity\Group;
use Kapcus\DbChanger\Entity\User;
use Kapcus\DbChanger\Entity\UserGroup;
use Kapcus\DbChanger\Model\Exception\ConfigurationException;

class Configurator implements IConfigurator
{
	/**
	 * @var \Kapcus\DbChanger\Entity\Environment[]
	 */
	private $environments = [];

	/**
	 * @var \Kapcus\DbChanger\Entity\Group[]
	 */
	private $groups = [];

	/**
	 * @var string[]
	 */
	private $groupNames = [];

	/**
	 * @var \Kapcus\DbChanger\Entity\User[]
	 */
	private $users = [];

	public function __construct($config)
	{
		if (!is_array($config)) {
			throw new ConfigurationException('Array of config parameters is expected.');
		}
		if (!isset($config['users'])) {
			throw new ConfigurationException('Configuration item \'users\' is expected to be set.');
		}
		if (!isset($config['groups'])) {
			throw new ConfigurationException('Configuration item \'users\' is expected to be set.');
		}
		if (!isset($config['environments'])) {
			throw new ConfigurationException('Configuration item \'users\' is expected to be set.');
		}
		$this->configureUsers($config['users']);
		$this->configureGroups($config['groups'], isset($config['manualGroups']) ? $config['manualGroups'] : []);
		$this->configureEnvironments($config['environments']);
	}

	/**
	 * @param string $code
	 *
	 * @return \Kapcus\DbChanger\Entity\Environment
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
	 * @return \Kapcus\DbChanger\Entity\Environment[]
	 */
	public function getEnvironments()
	{
		return $this->environments;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 */
	public function addEnvironment(Environment $environment)
	{
		$this->environments[] = $environment;
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\Group[]
	 */
	public function getGroups()
	{
		return $this->groups;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Group $group
	 */
	public function addGroup(Group $group)
	{
		$this->groups[] = $group;
		$this->addGroupName($group->getName());
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\User[]
	 */
	public function getUsers()
	{
		return $this->users;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\User $user
	 */
	public function addUser(User $user)
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

	/**
	 * @param string[] $groupNames
	 * @param string[] $manualGroups
	 */
	private function configureGroups(array $groupNames, array $manualGroups)
	{
		foreach ($groupNames as $groupName) {
			$group = new Group();
			$group->setName($groupName);
			$group->setIsManual((int) in_array($groupName, $manualGroups));
			$this->addGroup($group);
		}
	}

	/**
	 * @param string[] $userNames
	 */
	private function configureUsers(array $userNames)
	{
		foreach ($userNames as $userName) {
			$user = new User();
			$user->setName($userName);
			$this->addUser($user);
		}
	}

	/**
	 * @param array $environments
	 *
	 * @throws \Kapcus\DbChanger\Model\Exception\ConfigurationException
	 */
	private function configureEnvironments(array $environments)
	{
		foreach ($environments as $envCode => $envData) {
			$environment = new Environment();
			$environment->setCode($envCode);
			$environment->setName($envData['name']);
			$environment->setDescription($envData['description']);
			foreach ($envData['placeholders'] as $placeholder => $value) {
				$environment->addPlaceholder(new Placeholder($placeholder, $value));
			}

			foreach ($envData['groups'] as $groupName => $userNames) {
				$group = $this->getGroupByName($groupName);
				if ($group == null) {
					throw new ConfigurationException(
						sprintf('Invalid group %s defined in configuration for environment %s.', $groupName, $environment->getName())
					);
				}
				foreach ($userNames as $userName) {
					$user = $this->getUserByName($userName);
					if ($user == null) {
						throw new ConfigurationException(
							sprintf('Invalid user %s defined in configuration for environment %s.', $userName, $environment->getName())
						);
					}
					$userGroup = $environment->getUserGroup($groupName, $userName);
					if ($userGroup == null) {
						$userGroup = new UserGroup();
						$userGroup->setEnvironment($environment);
						$userGroup->setUser($user);
						$userGroup->setGroup($group);
						$environment->addUserGroup($userGroup);
					}
				}
			}
			$this->addEnvironment($environment);
		}
	}
}