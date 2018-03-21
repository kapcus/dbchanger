<?php

namespace Kapcus\DbChanger\Model;

class DbChange
{
	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var bool
	 */
	private $isLoaded;

	/**
	 * @var string
	 */
	private $code = null;

	/**
	 * @var string
	 */
	private $description = null;

	/**
	 * @var Fragment[]
	 */
	private $fragments = [];

	/**
	 * @var string
	 */
	private $hash;

	/**
	 * @var \Kapcus\DbChanger\Model\ILoader
	 */
	private $loader;

	/**
	 * @var \Kapcus\DbChanger\Model\IGenerator
	 */
	private $generator;

	public function __construct($id, $code)
	{
		$this->setId($id);
		$this->setCode($code);
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
	 * @return \Kapcus\DbChanger\Model\Fragment[]
	 */
	public function getFragments()
	{
		return $this->fragments;
	}

	/**
	 * @return \Kapcus\DbChanger\Model\Fragment[]
	 */
	public function getAutomaticFragments() {
		return array_filter($this->getFragments(), function(Fragment $frag) {
			return !$frag->getGroup()->isManual();
		});
	}

	/**
	 * @return \Kapcus\DbChanger\Model\Fragment[]
	 */
	public function getManualFragments() {
		return array_filter($this->getFragments(), function(Fragment $frag) {
			return $frag->getGroup()->isManual();
		});
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Fragment $fragment
	 */
	public function addFragment(Fragment $fragment)
	{
		$this->fragments[] = $fragment;
		//usort($this->fragments, [$this, 'compareFragments']);
		$this->recalculateHash();
	}

	public function hasFragment() {
		return count($this->fragments) > 0;
	}

	/**
	 * @param string $groupName
	 *
	 * @return \Kapcus\DbChanger\Model\Fragment|null
	 */
	public function getFragmentByGroupName($groupName) {
		foreach ($this->getFragments() as $fragment) {
			if ($fragment->getGroup()->getName() == $groupName) {
				return $fragment;
			}
		}
		return null;
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

	private function recalculateHash() {
		$dbChangeHash = '';
		/**
		 * @var \Kapcus\DbChanger\Model\Fragment
		 */
		foreach($this->fragments as $fragment) {
			$dbChangeHash .= $fragment->getHash();
		}
		$this->setHash(md5($dbChangeHash));
	}

	private function compareFragments(Fragment $fragment, Fragment $fragmentTwo) {
		return strcmp($fragment->getGroup()->getName(), $fragmentTwo->getGroup()->getName());
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
		foreach ($this->getFragments() as $fragment) {
			if (!$fragment->isPersistent()) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @return \Kapcus\DbChanger\Model\ILoader
	 */
	public function getLoader()
	{
		return $this->loader;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\ILoader $loader
	 */
	public function setLoader($loader)
	{
		$this->loader = $loader;
	}

	/**
	 * @return \Kapcus\DbChanger\Model\IGenerator
	 */
	public function getGenerator()
	{
		return $this->generator;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\IGenerator $generator
	 */
	public function setGenerator($generator)
	{
		$this->generator = $generator;
	}

	/**
	 * @return bool
	 */
	public function isLoaded()
	{
		return $this->isLoaded;
	}

	/**
	 * @param bool $isLoaded
	 */
	public function setIsLoaded($isLoaded)
	{
		$this->isLoaded = $isLoaded;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\Group[] $groups
	 *
	 * @throws \Kapcus\DbChanger\Model\Exception\DbChangeException
	 */
	public function load(array $groups) {
		$this->getLoader()->loadExistingDbChange($this, $groups);
		$this->setIsLoaded(true);
	}

	public function generate(Environment $environment) {
		foreach($this->getFragments() as $fragment) {
			$this->getGenerator()->generateFragmentContent($environment, $fragment);
		}
	}




}