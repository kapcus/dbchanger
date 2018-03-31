<?php

namespace Kapcus\DbChanger\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="DBCH_INSTALLED_FRAGMENT")
 */
class InstalledFragment
{
	const STATUS_NEW = 1;

	const STATUS_PENDING = 2;

	const STATUS_INSTALLED = 3;

	const STATUS_ROLLEDBACK = 4;

	const STATUS_CANCELLED = 5;


	private static $activeStatuses = [
		self::STATUS_NEW,
		self::STATUS_PENDING
	];
	/**
	 * @var array
	 */
	private static $statuses;

	/**
	 * @var string[]
	 */
	private static $shortcuts;

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="SEQUENCE")
	 * @ORM\SequenceGenerator(sequenceName="DBCH_IF_SEQ")
	 *
	 * @var int
	 */
	protected $id;

	/**
	 *
	 * @ORM\Column(type="text")
	 *
	 * @var string
	 */
	protected $content;

	/**
	 *
	 * @ORM\Column(type="datetime", name="done_at")
	 *
	 * @var \DateTime
	 */
	protected $doneAt;

	/**
	 *
	 * @ORM\Column(type="integer")
	 *
	 * @var int
	 */
	protected $status;

	/**
	 * @ORM\ManyToOne(targetEntity="Installation", inversedBy="installedFragments", cascade={"persist", "remove"})
	 *
	 * @var \Kapcus\DbChanger\Entity\Installation
	 **/
	protected $installation;

	/**
	 * @ORM\ManyToOne(targetEntity="UserGroup", inversedBy="installedFragments", cascade={"persist", "remove"})
	 *
	 * @var \Kapcus\DbChanger\Entity\UserGroup
	 **/
	protected $userGroup;

	/**
	 * @ORM\ManyToOne(targetEntity="Fragment", inversedBy="installedFragments", cascade={"persist", "remove"})
	 *
	 * @var \Kapcus\DbChanger\Entity\Fragment
	 **/
	protected $fragment;

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
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @param string $content
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}

	/**
	 * @return \DateTime
	 */
	public function getDoneAt()
	{
		return $this->doneAt;
	}

	/**
	 * @param \DateTime $doneAt
	 */
	public function setDoneAt($doneAt)
	{
		$this->doneAt = $doneAt;
	}

	/**
	 * @return int
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param int $status
	 */
	public function setStatus($status)
	{
		$this->status = $status;
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\Installation
	 */
	public function getInstallation()
	{
		return $this->installation;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Installation $installation
	 */
	public function setInstallation($installation)
	{
		$this->installation = $installation;
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\UserGroup
	 */
	public function getUserGroup()
	{
		return $this->userGroup;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\UserGroup $userGroup
	 */
	public function setUserGroup($userGroup)
	{
		$this->userGroup = $userGroup;
	}

	/**
	 * @return \Kapcus\DbChanger\Entity\Fragment
	 */
	public function getFragment()
	{
		return $this->fragment;
	}

	/**
	 * @param \Kapcus\DbChanger\Entity\Fragment $fragment
	 */
	public function setFragment($fragment)
	{
		$this->fragment = $fragment;
	}

	/**
	 * @return array
	 */
	public static function getStatuses()
	{
		if (!isset(self::$statuses)) {
			self::$statuses = [
				self::STATUS_NEW => '(N)ew',
				self::STATUS_PENDING => '(P)ending',
				self::STATUS_INSTALLED => '(I)nstalled',
				self::STATUS_ROLLEDBACK => '(R)olled back',
				self::STATUS_CANCELLED => '(C)ancelled',
			];

			self::$shortcuts = [];
			foreach(self::$statuses as $key => $value) {
				/*if (isset($shortcuts[$value[1]])) {
					throw new \Exception('Installed Fragment statuses must have unique shortcuts.');
				}*/
				self::$shortcuts[$value[1]] = $key;
			}
		}

		return self::$statuses;
	}

	/**
	 * @param string $shortcut
	 *
	 * @return null|int
	 */
	public static function getStatusByShortcut($shortcut)
	{
		$shortcut = strtoupper($shortcut);

		return isset(self::$shortcuts[$shortcut]) ? self::$shortcuts[$shortcut] : null;
	}

	/**
	 * @return string
	 */
	public static function getStatusNameString() {
		return implode(', ', array_values(InstalledFragment::getStatuses()));
	}

	/**
	 * @return int[]
	 */
	public static function getActiveStatuses() {
		return self::$activeStatuses;
	}

	/**
	 * @param int $key
	 *
	 * @return string|null
	 */
	public static function getStatusName($key) {
		return isset(self::$statuses[$key]) ? str_replace(['(', ')'], '', self::$statuses[$key]) : null;
	}

	/**
	 * @param int $key
	 *
	 * @return string|null
	 */
	public static function getStatusShortcut($key) {
		return isset(self::$statuses[$key]) ? self::$statuses[$key][1] : null;
	}
}