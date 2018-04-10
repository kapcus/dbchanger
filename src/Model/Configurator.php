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
	 * @var string[]
	 */
	private $configData;

	private $isSetup = false;

	public function __construct($configData)
	{
		$this->configData = $configData;
	}

	/**
	 * @throws \Kapcus\DbChanger\Model\Exception\ConfigurationException
	 */
	public function setup()
	{
		if ($this->isSetup()) {
			return;
		}
		if (!is_array($this->configData) || empty($this->configData)) {
			throw new ConfigurationException('Configuration item \'datamodel\' is empty, \'groups\' and \'environments\' must be defined within.');
		}
		if (!isset($this->configData['groups']) || empty($this->configData['groups'])) {
			throw new ConfigurationException('Configuration item \'groups\' is expected to be set and not empty.');
		}
		if (!isset($this->configData['environments']) || empty($this->configData['environments'])) {
			throw new ConfigurationException('Configuration item \'environments\' is expected to be set and not empty.');
		}
		$this->configureGroups($this->configData['groups'], isset($this->configData['manualGroups']) ? $this->configData['manualGroups'] : []);
		$this->configureEnvironments($this->configData['environments']);

		$this->setIsSetup(true);
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\Environment[]
	 * @throws \Kapcus\DbChanger\Model\Exception\ConfigurationException
	 */
	public function getEnvironments()
	{
		$this->setup();

		return $this->environments;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 */
	private function addEnvironment(Environment $environment)
	{
		$this->environments[] = $environment;
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\Group[]
	 * @throws \Kapcus\DbChanger\Model\Exception\ConfigurationException
	 */
	public function getGroups()
	{
		$this->setup();

		return $this->groups;
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\Group[]
	 */
	private function getGroupsForConfiguration()
	{
		return $this->groups;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Group $group
	 */
	private function addGroup(Group $group)
	{
		$this->groups[] = $group;
		$this->addGroupName($group->getName());
	}

	/**
	 * @return string[]
	 * @throws \Kapcus\DbChanger\Model\Exception\ConfigurationException
	 */
	public function getGroupNames()
	{
		$this->setup();

		return $this->groupNames;
	}

	/**
	 * @param string $groupName
	 */
	private function addGroupName($groupName)
	{
		$this->groupNames[] = $groupName;
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
			$group->setIsManual((int)in_array($groupName, $manualGroups));
			$this->addGroup($group);
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
			$this->configureEnvironment($envCode, $envData);
		}
	}

	/**
	 *
	 * @param $data
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 */
	private function configureUsers($data, Environment $environment)
	{
		if (isset($data['users']) && is_array($data['users']) && !empty($data['users'])) {
			foreach ($data['users'] as $username => $password) {
				$user = new User();
				$user->setName($username);
				$user->setPassword($password);
				$user->setEnvironment($environment);
				$environment->addUser($user);
			}
		} elseif (isset($data['usernames']) && is_array($data['usernames']) && !empty($data['usernames']) && isset($data['passwords']) && is_array(
				$data['passwords']
			) && count($data['passwords']) == count($data['usernames'])) {
			foreach (array_combine($data['usernames'], $data['passwords']) as $username => $password) {
				$user = new User();
				$user->setName($username);
				$user->setPassword($password);
				$user->setEnvironment($environment);
				$environment->addUser($user);
			}
		} else {
			throw new ConfigurationException(sprintf('Either configuration environment item \'users\' or item pair \'usernames\' and \'passwords\' is expected to be set.'));
		}
	}

	private function checkMandatoryEnvironmentField(
		$data,
		$fieldName,
		$isArray = false
	) {
		if (!isset($data[$fieldName]) || ($isArray && empty($isArray))) {
			throw new ConfigurationException(sprintf('Configuration environment item \'%s\' is expected to be set.', $fieldName));
		}
	}

	/**
	 * @param $environmentCode
	 * @param $envData
	 *
	 * @throws \Kapcus\DbChanger\Model\Exception\ConfigurationException
	 */
	private
	function configureEnvironment(
		$environmentCode,
		$envData
	) {
		$environment = new Environment();
		$environment->setCode($environmentCode);
		$this->checkMandatoryEnvironmentField($envData, 'name');
		$environment->setName($envData['name']);
		if (isset($data['description'])) {
			$environment->setDescription($envData['description']);
		}
		$this->checkMandatoryEnvironmentField($envData, 'port');
		$environment->setPort($envData['port']);
		$this->checkMandatoryEnvironmentField($envData, 'dbname');
		$environment->setDatabaseName($envData['dbname']);
		$this->checkMandatoryEnvironmentField($envData, 'hostname');
		$environment->setHost($envData['hostname']);
		//$this->checkMandatoryEnvironmentField($envData, 'users', true);
		$this->configureUsers($envData, $environment);

		if (isset($envData['placeholders']) && is_array($envData['placeholders'])) {
			foreach ($envData['placeholders'] as $placeholderCode => $value) {
				$placeholder = new \Kapcus\DbChanger\Entity\Placeholder();
				$placeholder->setCode($placeholderCode);
				$placeholder->setTranslatedValue($value);
				$placeholder->setEnvironment($environment);
				$environment->addPlaceholder($placeholder);
			}
		}

		$this->checkMandatoryEnvironmentField($envData, 'groups', true);
		foreach ($envData['groups'] as $groupName => $userNames) {
			$group = Util::getGroupByName($this->getGroupsForConfiguration(), $groupName);
			if ($group == null) {
				throw new ConfigurationException(
					sprintf(
						'Invalid group %s defined in configuration for environment %s - check if group is defined in \'groups\'.',
						$groupName,
						$environment->getName()
					)
				);
			}
			foreach ($userNames as $userName) {
				$user = $environment->getUserByName($userName);
				if ($user == null) {
					throw new ConfigurationException(
						sprintf(
							'Undefined user %s in group %s specified in environment %s. Add user into users section first.',
							$userName,
							$groupName,
							$environment->getName()
						)
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

	/**
	 * @param string $environmentCode
	 *
	 * @return array
	 * @throws \Kapcus\DbChanger\Model\Exception\ConfigurationException
	 */
	public
	function getEnvironmentConnectionConfigurations(
		$environmentCode
	) {
		$this->setup();
		$environment = Util::getEnvironmentByCode($this->getEnvironments(), $environmentCode);
		if ($environment == null) {
			throw new ConfigurationException(
				sprintf('Environment with code %s is not configured, check your configuration file.', $environmentCode)
			);
		}
		$configurations = [];
		foreach ($environment->getUsers() as $user) {
			$configuration = new ConnectionConfiguration();
			$configuration->setPort($environment->getPort());
			$configuration->setUsername($user->getName());
			$configuration->setPassword($user->getPassword());
			$configuration->setPort($environment->getPort());
			$configuration->setDatabaseName($environment->getDatabaseName());
			$configuration->setHostname($environment->getHost());
			$configurations[] = $configuration;
		}

		return $configurations;
	}

	/**
	 * @return bool
	 */
	private
	function isSetup()
	{
		return $this->isSetup;
	}

	/**
	 * @param bool $isSetup
	 */
	private
	function setIsSetup(
		$isSetup
	) {
		$this->isSetup = $isSetup;
	}
}