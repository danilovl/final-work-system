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

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use FinalWork\FinalWorkBundle\Constant\TranslationConstant;
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};

/**
 * @ORM\Table(name="work_type")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="cache_long_time")
 * @Gedmo\Loggable
 */
class WorkType
{
    use IdTrait;
    use SimpleInformationTrait;
    use CreateUpdateAbleTrait;

    /**
     * @var string|null
     *
     * @ORM\Column(name="shortcut", type="string", nullable=false)
     * @Gedmo\Versioned
     */
    private $shortcut;

    /**
     * @var Collection|Work[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Work", mappedBy="type")
     */
    private $works;

    /**
     * WorkType constructor.
     */
    public function __construct()
    {
        $this->works = new ArrayCollection;
    }

    /**
     * @return string|null
     */
    public function getShortcut(): ?string
    {
        return $this->shortcut;
    }

    /**
     * @param string $shortcut
     */
    public function setShortcut(string $shortcut): void
    {
        $this->shortcut = $shortcut;
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
