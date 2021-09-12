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

use App\Repository\WorkStatusRepository;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Constant\TranslationConstant;
use App\Entity\Traits\{
    IdTrait,
    ConstantAwareTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};

/**
 * @Gedmo\Loggable
 */
#[ORM\Table(name: 'work_status')]
#[ORM\Entity(repositoryClass: WorkStatusRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
class WorkStatus
{
    use IdTrait;
    use SimpleInformationTrait;
    use ConstantAwareTrait;
    use CreateUpdateAbleTrait;

    /**
     * @Gedmo\Versioned
     */
    #[ORM\Column(name: 'color', type: Types::STRING, nullable: false)]
    private ?string $color = null;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: Work::class)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?Collection $works = null;

    public function __construct()
    {
        $this->works = new ArrayCollection;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
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

    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
