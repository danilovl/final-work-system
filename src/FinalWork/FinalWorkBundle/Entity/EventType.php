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

use Doctrine\Common\Collections\Collection;
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
 * @ORM\Table(name="event_type")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="cache_long_time")
 * @Gedmo\Loggable
 */
class EventType
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
     * @var boolean
     *
     * @ORM\Column(name="registrable", type="boolean", options={"default":"0"})
     * @Gedmo\Versioned
     */
    private $registrable = false;

    /**
     * @var Collection|Event[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Event", mappedBy="type")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $events;

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
     * @return bool
     */
    public function isRegistrable(): ?bool
    {
        return $this->registrable;
    }

    /**
     * @param bool $registrable
     */
    public function setRegistrable(bool $registrable): void
    {
        $this->registrable = $registrable;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    /**
     * @param Collection $events
     */
    public function setEvents(Collection $events): void
    {
        $this->events = $events;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
