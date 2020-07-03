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

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Constant\TranslationConstant;
use App\Entity\Traits\{
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
     * @ORM\ManyToOne(targetEntity="App\Entity\EventSchedule", inversedBy="templates")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_schedule_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?EventSchedule $schedule = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EventType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_type_id", referencedColumnName="id", nullable=false)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?EventType $type = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EventAddress")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_address_id", referencedColumnName="id", nullable=true)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?EventAddress $address = null;

    /**
     * @ORM\Column(name="day", type="integer",  nullable=false)
     * @Gedmo\Versioned
     */
    private ?int $day = null;

    /**
     * @ORM\Column(name="name", type="string", nullable=true)
     * @Gedmo\Versioned
     */
    private ?string $name = null;

    /**
     * @ORM\Column(name="start", type="time", nullable=false)
     * @Gedmo\Versioned
     */
    private ?DateTime $start = null;

    /**
     * @ORM\Column(name="end", type="time", nullable=false)
     * @Gedmo\Versioned
     */
    private ?DateTime $end = null;

    public function getSchedule(): ?EventSchedule
    {
        return $this->schedule;
    }

    public function setSchedule(EventSchedule $schedule): void
    {
        $this->schedule = $schedule;
    }

    public function getType(): ?EventType
    {
        return $this->type;
    }

    public function setType(EventType $type): void
    {
        $this->type = $type;
    }

    public function getAddress(): ?EventAddress
    {
        return $this->address;
    }

    public function setAddress(EventAddress $address): void
    {
        $this->address = $address;
    }

    public function getDay(): ?int
    {
        return $this->day;
    }

    public function setDay(int $day): void
    {
        $this->day = $day;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getStart(): ?DateTime
    {
        return $this->start;
    }

    public function setStart(DateTime $start): void
    {
        $this->start = $start;
    }

    public function getEnd(): ?DateTime
    {
        return $this->end;
    }

    public function setEnd(DateTime $end): void
    {
        $this->end = $end;
    }

    public function __toString(): string
    {
        return TranslationConstant::EMPTY;
    }
}
