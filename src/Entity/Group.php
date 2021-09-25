<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Entity;

use App\Constant\TranslationConstant;
use App\Entity\Traits\{
    IdTrait,
    TimestampAbleTrait
};
use App\Repository\UserGroupRepository;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'user_group')]
#[ORM\Entity(repositoryClass: UserGroupRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Group
{
    use IdTrait;
    use TimestampAbleTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: Types::STRING, nullable: false)]
    protected ?string $name = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'groups')]
    protected Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection;
    }

    public function getName(): ?string
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
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
