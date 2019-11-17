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

namespace FinalWork\FinalWorkBundle\Entity;

use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use FinalWork\FinalWorkBundle\Constant\TranslationConstant;
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    ConstantAwareTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};

/**
 * @ORM\Table(name="work_status")
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\WorkStatusRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="cache_long_time")
 * @Gedmo\Loggable
 */
class WorkStatus
{
    use IdTrait;
    use SimpleInformationTrait;
    use ConstantAwareTrait;
    use CreateUpdateAbleTrait;

    /**
     * @var string|null
     *
     * @ORM\Column(name="color", type="string", nullable=false)
     * @Gedmo\Versioned
     */
    private $color;

    /**
     * @var Collection|Work[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Work", mappedBy="status")
     */
    private $works;

    /**
     * WorkStatus constructor.
     */
    public function __construct()
    {
        $this->works = new ArrayCollection;
    }

    /**
     * @return string|null
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
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

    /**
     * @param Collection $works
     */
    public function setWorks(Collection $works): void
    {
        $this->works = $works;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
