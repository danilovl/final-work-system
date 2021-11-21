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

namespace App\Model\ApiUserRule\Entity;

use App\Model\ApiUser\Entity\ApiUser;
use App\Model\ApiUserRule\Repository\ApiUserRuleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\{
    IdTrait,
    CreateUpdateAbleTrait
};
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\Loggable()
 */
#[ORM\Table(name: 'api_user_rule')]
#[ORM\Entity(repositoryClass: ApiUserRuleRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ApiUserRule
{
    use IdTrait;
    use CreateUpdateAbleTrait;

    #[ORM\Column(name: 'range_ip', type: Types::STRING, nullable: false)]
    protected ?string $rangeIp = null;

    #[ORM\OneToMany(mappedBy: 'rules', targetEntity: ApiUser::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'api_user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
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
