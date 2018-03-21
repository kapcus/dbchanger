<?php

namespace Kapcus\DbChanger\Model;

class DbChangeFactory
{
	/**
	 * @var \Kapcus\DbChanger\Model\ILoader
	 */
	private $loader;

	/**
	 * @var \Kapcus\DbChanger\Model\IGenerator
	 */
	private $generator;

	public function __construct(ILoader $loader, IGenerator $generator)
	{
		$this->loader = $loader;
		$this->generator = $generator;
	}

	public function createDbChange($code, array $groups) {
		$dbChange = new DbChange(null, $code);
		$dbChange->setLoader($this->loader);
		$dbChange->setGenerator($this->generator);

		$dbChange->load($groups);
		return $dbChange;
	}

	/**
	 * @param int $id
	 * @param string $code
	 *
	 * @return \Kapcus\DbChanger\Model\DbChange
	 */
	public function createPersistentDbChange($id, $code) {
		$dbChange = new DbChange($id, $code);
		$dbChange->setLoader($this->loader);
		$dbChange->setGenerator($this->generator);

		return $dbChange;
	}

}