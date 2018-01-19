<?php

namespace Kapcus\DbChanger\Model;

interface IGenerator
{
	/**
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 * @param \Kapcus\DbChanger\Model\DbChange $dbChange
	 *
	 * @return bool
	 */
	//public function generateDbChange(Environment $environment, DbChange $dbChange);

	/**
	 * @param \Kapcus\DbChanger\Model\Environment $environment
	 * @param \Kapcus\DbChanger\Model\Fragment $dbChangeFragment
	 *
	 * @return mixed
	 */
	public function generateFragmentContent(Environment $environment, Fragment $dbChangeFragment);
}