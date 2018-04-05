<?php

namespace Kapcus\DbChanger\Model;

use Kapcus\DbChanger\Entity\DbChange;
use Kapcus\DbChanger\Entity\Environment;
use Kapcus\DbChanger\Entity\Fragment;
use Kapcus\DbChanger\Entity\UserGroup;

interface IGenerator
{
	/**
	 * @return void
	 */
	public function enableDebug();

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\DbChange[] $dbChange
	 *
	 */
	public function generateDbChangesIntoFile(Environment $environment, array $dbChange);

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\DbChange $dbChange
	 *
	 */
	public function generateDbChangeIntoFile(Environment $environment, DbChange $dbChange);

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\Fragment $fragment
	 *
	 */
	public function generateFragmentIntoFile(Environment $environment, Fragment $fragment);

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\Fragment $dbChangeFragment
	 * @param \Kapcus\DbChanger\Entity\UserGroup $userGroup
	 *
	 * @return string fragment content
	 */
	public function getFragmentContent(Environment $environment, Fragment $dbChangeFragment, UserGroup $userGroup);

	/**
	 * @return string
	 */
	public function getOutputDirectory();
}