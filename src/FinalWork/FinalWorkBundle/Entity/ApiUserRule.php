<?php declare(strict_types=1);

/**
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace FinalWork\FinalWorkBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    CreateUpdateAbleTrait
};
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="api_user_rule")
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\ApiUserRuleRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Gedmo\Loggable()
 */
class ApiUserRule
{
	use IdTrait;
	use CreateUpdateAbleTrait;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="range_ip", type="string", nullable=false)
	 */
	protected $rangeIp;

	/**
	 * @var ApiUser
	 *
	 * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\ApiUser", inversedBy="rules", cascade={"persist"})
	 * @ORM\JoinColumn(name="api_user_id", nullable=false, referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $apiUser;

	/**
	 * @return string
	 */
	public function getRangeIp(): string
	{
		return $this->rangeIp;
	}

	/**
	 * @param string $rangeIp
	 */
	public function setRangeIp(string $rangeIp): void
	{
		$this->rangeIp = $rangeIp;
	}

	/**
	 * @return ApiUser
	 */
	public function getApiUser(): ApiUser
	{
		return $this->apiUser;
	}

	/**
	 * @param ApiUser $apiUser
	 */
	public function setApiUser(ApiUser $apiUser): void
	{
		$this->apiUser = $apiUser;
	}
}
