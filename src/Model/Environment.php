<?php

namespace Kapcus\DbChanger\Model;

class Environment
{
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $code;

	/**
	 * @var \Kapcus\DbChanger\Model\EnvironmentGroup[]
	 */
	private $groups = [];

	/**
	 * @var \Kapcus\DbChanger\Model\Placeholder[]
	 */
	private $placeholders = [];

	public function __construct($code, $name)
	{
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
	}




}