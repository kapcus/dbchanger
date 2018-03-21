<?php

namespace Kapcus\DbChanger\Model;

use Kapcus\DbChanger\Entity\Environment;

interface IGenerator
{
	/**
	 * @param \Kapcus\DbChanger\Entity\Environment $environment
	 * @param \Kapcus\DbChanger\Entity\DbChange $dbChange
	 *
	 * @return bool
	 */
	public function generateDbChange(Environment $environment, \Kapcus\DbChanger\Entity\DbChange $dbChange);

	/**
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 * @param \Kapcus\DbChanger\Model\Fragment $dbChangeFragment
	 *
	 * @return mixed
	 */
	//public function generateFragmentContent(Environment $environment, Fragment $dbChangeFragment);
}