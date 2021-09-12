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

use App\Repository\WorkCategoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use App\Constant\TranslationConstant;
use App\Entity\Traits\{
    IdTrait,
    IsOwnerTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};

/**
 * @Gedmo\Loggable
 */
#[ORM\Table(name: 'work_category')]
#[ORM\Entity(repositoryClass: WorkCategoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
class WorkCategory
{
    use IdTrait;
    use SimpleInformationTrait;
    use CreateUpdateAbleTrait;
    use IsOwnerTrait;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'workCategoriesOwner')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?User $owner = null;

    #[ORM\ManyToMany(targetEntity: Work::class, mappedBy: 'categories')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?Collection $works = null;

    /**
     * @Gedmo\Versioned
     */
    #[ORM\Column(name: 'sorting', type: Types::STRING, nullable: true)]
    private ?string $sorting = null;

    public function __construct()
    {
        $this->works = new ArrayCollection;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @return Collection|Work[]
     */
    public function getWorks(): Collection
    {
        return $this->works;
    }

    public function setWorks(Collection $works): void
    {
        $this->works = $works;
    }

    public function getSorting(): ?string
    {
        return $this->sorting;
    }

    public function setSorting(?string $sorting): void
    {
        $this->sorting = $sorting;
    }

    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
