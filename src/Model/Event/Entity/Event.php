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

namespace App\Model\Event\Entity;

use App\Model\Comment\Entity\Comment;
use App\Model\Event\Repository\EventRepository;
use App\Model\EventAddress\Entity\EventAddress;
use App\Model\EventParticipant\Entity\EventParticipant;
use App\Model\EventType\Entity\EventType;
use App\Model\SystemEvent\Entity\SystemEvent;
use App\Model\User\Entity\User;
use DateTime;
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
    IsOwnerTrait,
    CreateUpdateAbleTrait
};

/**
 * @Gedmo\Loggable
 */
#[ORM\Table(name: 'event')]
#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
class Event
{
    use IdTrait;
    use CreateUpdateAbleTrait;
    use IsOwnerTrait;

    #[ORM\ManyToOne(targetEntity: EventType::class, fetch: 'EAGER', inversedBy: 'events')]
    #[ORM\JoinColumn(name: 'event_type_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?EventType $type = null;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER', inversedBy: 'eventsOwner')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?User $owner = null;

    #[ORM\ManyToOne(targetEntity: EventAddress::class, fetch: 'EAGER', inversedBy: 'events')]
    #[ORM\JoinColumn(name: 'event_address_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?EventAddress $address = null;

    /**
     * @Gedmo\Versioned
     */
    #[ORM\Column(name: 'name', type: Types::STRING, nullable: true)]
    private ?string $name = null;

    /**
     * @Gedmo\Versioned
     */
    #[ORM\Column(name: 'start', type: Types::DATETIME_MUTABLE, nullable: false)]
    private ?DateTime $start = null;

    /**
     * @Gedmo\Versioned
     */
    #[ORM\Column(name: 'end', type: Types::DATETIME_MUTABLE, nullable: false)]
    private ?DateTime $end = null;

    #[ORM\OneToOne(mappedBy: 'event', targetEntity: EventParticipant::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?EventParticipant $participant = null;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Comment::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $comment;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: SystemEvent::class)]
    private Collection $systemEvents;

    public function __construct()
    {
        $this->comment = new ArrayCollection;
        $this->systemEvents = new ArrayCollection;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComment(): Collection
    {
        return $this->comment;
    }

    public function setComment(Collection $comment): void
    {
        $this->comment = $comment;
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

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
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

    public function getParticipant(): ?EventParticipant
    {
        return $this->participant;
    }

    public function setParticipant(?EventParticipant $participant): void
    {
        $this->participant = $participant;
    }

    public function toString(): string
    {
        $event = null;

        if ($this->getName()) {
            $event = $this->getName();
        }

        if ($this->getAddress()) {
            $address = $event ? sprintf("\n%s", $this->getAddress()->getName()) : $this->getAddress()->getName();
            $event .= $address;
        }

        if ($this->getParticipant()) {
            $participant = $event ? sprintf("\n%s", $this->getParticipant()->toString()) : $this->getParticipant()->toString();
            $event .= $participant;
        }

        return $event ?: TranslationConstant::EMPTY;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
