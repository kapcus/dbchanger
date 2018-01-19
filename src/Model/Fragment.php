<?php

namespace Kapcus\DbChanger\Model;

class Fragment
{
	/**
	 * @var string
	 */
	private $filename = null;

	/**
	 * @var string
	 */
	private $filePath = null;

	/**
	 * @var \Kapcus\DbChanger\Model\Group
	 */
	private $group = null;

	/**
	 * @var string
	 */
	private $hash;

	/**
	 * @var string
	 */
	private $template = null;

	/**
	 * @var string[] content for particular user
	 */
	private $userContents = [];

	/**
	 * @var \Kapcus\DbChanger\Model\DbChange
	 */
	private $dbChange;

	/**
	 * @var \Kapcus\DbChanger\Model\User[]
	 */
	private $users = [];

	/**
	 * @var int
	 */
	private $id;

	public function __construct($id, DbChange $dbChange, Group $group)
	{
		$this->setId($id);
		$this->setDbChange($dbChange);
		$this->setGroup($group);
	}

	/**
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * @param string $filename
	 */
	public function setFilename($filename)
	{
		$this->filename = $filename;
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
	public function setGroup(Group $group)
	{
		$this->group = $group;
	}

	/**
	 * @return string
	 */
	public function getHash()
	{
		return $this->hash;
	}

	/**
	 * @param string $hash
	 */
	public function setHash($hash)
	{
		$this->hash = $hash;
	}

	/**
	 * @return string
	 */
	public function getFilePath()
	{
		return $this->filePath;
	}

	/**
	 * @param string $filePath
	 */
	public function setFilePath($filePath)
	{
		$this->filePath = $filePath;
	}

	/**
	 * @return string
	 */
	public function getTemplate()
	{
		if ($this->template == null) {
			$this->setTemplate(file_get_contents($this->getfilePath()));
		}
		return $this->template;
	}

	/**
	 * @param string $template
	 */
	public function setTemplate($template)
	{
		$this->template = $template;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\User $user
	 *
	 * @return string
	 */
	public function getUserContent(User $user)
	{
		return $this->userContents[$user->getName()];
	}

	/**
	 * @param \Kapcus\DbChanger\Model\User $user
	 * @param string $userContent
	 */
	public function setUserContent(User $user, $userContent)
	{
		$this->addUser($user);
		$this->userContents[$user->getName()] = $userContent;
	}

	/**
	 * @return \Kapcus\DbChanger\Model\DbChange
	 */
	public function getDbChange()
	{
		return $this->dbChange;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\DbChange $dbChange
	 */
	public function setDbChange($dbChange)
	{
		$this->dbChange = $dbChange;
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
	 * @return \Kapcus\DbChanger\Model\User
	 */
	public function getUserByName($userName)
	{
		return $this->users[$userName];
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
	
}