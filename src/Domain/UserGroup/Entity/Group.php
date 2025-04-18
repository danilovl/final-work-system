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

namespace App\Domain\UserGroup\Entity;

use App\Application\Traits\Entity\{
    IdTrait,
    CreateUpdateAbleTrait
};
use App\Domain\User\Entity\User;
use App\Domain\UserGroup\Repository\UserGroupRepository;
use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection
};
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'user_group')]
#[ORM\Entity(repositoryClass: UserGroupRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Group
{
    use IdTrait;
    use CreateUpdateAbleTrait;

    #[ORM\Column(name: 'name', type: Types::STRING, nullable: false)]
    protected string $name;

    /** @var Collection<User> */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'groups')]
    protected Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
