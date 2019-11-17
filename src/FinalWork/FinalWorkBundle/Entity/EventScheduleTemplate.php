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

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use FinalWork\FinalWorkBundle\Constant\TranslationConstant;
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    CreateUpdateAbleTrait
};

/**
 * @ORM\Table(name="event_schedule_template")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
 * @Gedmo\Loggable
 */
class EventScheduleTemplate
{
    use IdTrait;
    use CreateUpdateAbleTrait;

    /**
     * @var EventSchedule|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\EventSchedule", inversedBy="templates")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_schedule_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $schedule;

    /**
     * @var EventType|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\EventType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_type_id", referencedColumnName="id", nullable=false)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $type;

    /**
     * @var EventAddress|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\EventAddress")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_address_id", referencedColumnName="id", nullable=true)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $address;

    /**
     * @var int|null
     *
     * @ORM\Column(name="day", type="integer",  nullable=false)
     * @Gedmo\Versioned
     */
    private $day;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", nullable=true)
     * @Gedmo\Versioned
     */
    private $name;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="start", type="time", nullable=false)
     * @Gedmo\Versioned
     */
    private $start;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="end", type="time", nullable=false)
     * @Gedmo\Versioned
     */
    private $end;

    /**
     * @return EventSchedule|null
     */
    public function getSchedule(): ?EventSchedule
    {
        return $this->schedule;
    }

    /**
     * @param EventSchedule $schedule
     */
    public function setSchedule(EventSchedule $schedule): void
    {
        $this->schedule = $schedule;
    }

    /**
     * @return EventType|null
     */
    public function getType(): ?EventType
    {
        return $this->type;
    }

    /**
     * @param EventType $type
     */
    public function setType(EventType $type): void
    {
        $this->type = $type;
    }

    /**
     * @return EventAddress|null
     */
    public function getAddress(): ?EventAddress
    {
        return $this->address;
    }

    /**
     * @param EventAddress $address
     */
    public function setAddress(EventAddress $address): void
    {
        $this->address = $address;
    }

    /**
     * @return int|null
     */
    public function getDay(): ?int
    {
        return $this->day;
    }

    /**
     * @param int $day
     */
    public function setDay(int $day): void
    {
        $this->day = $day;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return DateTime|null
     */
    public function getStart(): ?DateTime
    {
        return $this->start;
    }

    /**
     * @param DateTime $start
     */
    public function setStart(DateTime $start): void
    {
        $this->start = $start;
    }

    /**
     * @return DateTime|null
     */
    public function getEnd(): ?DateTime
    {
        return $this->end;
    }

    /**
     * @param DateTime $end
     */
    public function setEnd(DateTime $end): void
    {
        $this->end = $end;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return TranslationConstant::EMPTY;
    }
}
