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
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Constant\TranslationConstant;
use App\Entity\Traits\{
    IdTrait,
    CreateUpdateAbleTrait
};

/**
 * @Gedmo\Loggable
 */
#[ORM\Table(name: 'event_schedule_template')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
class EventScheduleTemplate
{
    use IdTrait;
    use CreateUpdateAbleTrait;

    #[ORM\ManyToOne(targetEntity: EventSchedule::class, inversedBy: 'templates')]
    #[ORM\JoinColumn(name: 'event_schedule_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?EventSchedule $schedule = null;

    #[ORM\ManyToOne(targetEntity: EventType::class)]
    #[ORM\JoinColumn(name: 'event_type_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?EventType $type = null;

    #[ORM\ManyToOne(targetEntity: EventAddress::class)]
    #[ORM\JoinColumn(name: 'event_address_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?EventAddress $address = null;

    /**
     * @Gedmo\Versioned
     */
    #[ORM\Column(name: 'day', type: Types::INTEGER, nullable: false)]
    private ?int $day = null;

    /**
     * @Gedmo\Versioned
     */
    #[ORM\Column(name: 'name', type: Types::STRING, nullable: true)]
    private ?string $name = null;

    /**
     * @Gedmo\Versioned
     */
    #[ORM\Column(name: 'start', type: Types::TIME_MUTABLE, nullable: false)]
    private ?DateTime $start = null;

    /**
     * @Gedmo\Versioned
     */
    #[ORM\Column(name: 'end', type: Types::TIME_MUTABLE, nullable: false)]
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
