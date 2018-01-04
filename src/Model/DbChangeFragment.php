<?php

namespace Kapcus\DbChanger\Model;

class DbChangeFragment
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

	public function __construct($group, $filePath, $filename)
	{
		$this->setGroup($group);
		$this->setfilePath($filePath);
		$this->setFilename($filename);
		$content = $this->getContent();
		$this->setHash(md5($content));
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

	public function getContent()
	{
		return file_get_contents($this->getfilePath());
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
	
	
}