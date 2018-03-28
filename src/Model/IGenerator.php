<?php

namespace Kapcus\DbChanger\Model;

use Kapcus\DbChanger\Entity\DbChange;
use Kapcus\DbChanger\Entity\Environment;
use Kapcus\DbChanger\Entity\Fragment;
use Kapcus\DbChanger\Entity\UserGroup;

interface IGenerator
{
	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\DbChange[] $dbChange
	 *
	 * @return string
	 */
	public function generateDbChanges(Environment $environment, array $dbChange);

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\DbChange $dbChange
	 *
	 * @return string
	 */
	public function generateDbChange(Environment $environment, DbChange $dbChange);

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\Fragment $fragment
	 *
	 * @return string
	 */
	public function generateFragment(Environment $environment, Fragment $fragment);

	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\Fragment $dbChangeFragment
	 * @param \Kapcus\DbChanger\Entity\UserGroup $userGroup
	 *
	 * @return string
	 */
	public function generateDbChangeFragmentContent(Environment $environment, \Kapcus\DbChanger\Entity\Fragment $dbChangeFragment, UserGroup $userGroup);
}