<?php

namespace Kapcus\DbChanger\Model;

class DbChange
{
	/**
	 * @var string
	 */
	private $code = null;

	/**
	 * @var string
	 */
	private $description = null;

	/**
	 * @var DbChangeFragment[]
	 */
	private $fragments = [];

	/**
	 * @var string
	 */
	private $hash;

	public function __construct($code)
	{
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
	 * @return \Kapcus\DbChanger\Model\DbChangeFragment[]
	 */
	public function getFragments()
	{
		return $this->fragments;
	}

	/**
	 * @param \Kapcus\DbChanger\Model\DbChangeFragment $fragment
	 */
	public function addFragment(DbChangeFragment $fragment)
	{
		$this->fragments[] = $fragment;
		usort($this->fragments, [$this, 'compareFragments']);
		$this->recalculateHash();
	}

	public function hasFragment() {
		return count($this->fragments) > 0;
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
		 * @var \Kapcus\DbChanger\Model\DbChangeFragment
		 */
		foreach($this->fragments as $fragment) {
			$dbChangeHash .= $fragment->getHash();
		}
		$this->setHash(md5($dbChangeHash));
	}

	private function compareFragments(DbChangeFragment $fragment, DbChangeFragment $fragmentTwo) {
		return strcmp($fragment->getGroup()->getName(), $fragmentTwo->getGroup()->getName());
	}
}