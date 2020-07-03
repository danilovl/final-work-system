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

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\{
    IdTrait,
    CreateUpdateAbleTrait
};
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="api_user_rule")
 * @ORM\Entity(repositoryClass="App\Repository\ApiUserRuleRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Gedmo\Loggable()
 */
class ApiUserRule
{
    use IdTrait;
    use CreateUpdateAbleTrait;

    /**
     * @ORM\Column(name="range_ip", type="string", nullable=false)
     */
    protected ?string $rangeIp = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ApiUser", inversedBy="rules", cascade={"persist"})
     * @ORM\JoinColumn(name="api_user_id", nullable=false, referencedColumnName="id", onDelete="CASCADE")
     */
    protected ?ApiUser $apiUser = null;

    public function getRangeIp(): string
    {
        return $this->rangeIp;
    }

    public function setRangeIp(string $rangeIp): void
    {
        $this->rangeIp = $rangeIp;
    }

    public function getApiUser(): ApiUser
    {
        return $this->apiUser;
    }

    public function setApiUser(ApiUser $apiUser): void
    {
        $this->apiUser = $apiUser;
    }
}
