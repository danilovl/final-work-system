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
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use FinalWork\FinalWorkBundle\Constant\TranslationConstant;
use FinalWork\SonataUserBundle\Entity\User;
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    IsOwnerTrait,
    CreateUpdateAbleTrait
};

/**
 * @ORM\Table(name="event")
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\EventRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
 * @Gedmo\Loggable
 */
class Event
{
    use IdTrait;
    use CreateUpdateAbleTrait;
    use IsOwnerTrait;

    /**
     * @var EventType|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\EventType", inversedBy="events", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_type_id", referencedColumnName="id", nullable=false)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $type;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="eventsOwner", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $owner;

    /**
     * @var EventAddress|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\EventAddress", inversedBy="events", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_address_id", referencedColumnName="id", nullable=true)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $address;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", nullable=true)
     * @Gedmo\Versioned
     */
    private $name;

    /**
     * @var DateTime DateTime
     *
     * @ORM\Column(name="start", type="datetime", nullable=false)
     * @Gedmo\Versioned
     */
    private $start;

    /**
     * @var DateTime DateTime
     *
     * @ORM\Column(name="end", type="datetime", nullable=false)
     * @Gedmo\Versioned
     */
    private $end;

    /**
     * @var EventParticipant|null
     *
     * @ORM\OneToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\EventParticipant", mappedBy="event", cascade={"persist", "remove"})
     */
    private $participant;

    /**
     * @var Collection|Comment[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Comment", mappedBy="event", cascade={"persist", "remove"})
     */
    private $comment;

    /**
     * @var Collection|SystemEvent[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\SystemEvent", mappedBy="event")
     */
    private $systemEvents;

    /**
     * Event constructor.
     */
    public function __construct()
    {
        $this->comment = new ArrayCollection;
        $this->systemEvents = new ArrayCollection;
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

    /**
     * @param Collection $comment
     */
    public function setComment(Collection $comment): void
    {
        $this->comment = $comment;
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
     * @return User|null
     */
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     */
    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
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
     * @return EventParticipant|null
     */
    public function getParticipant(): ?EventParticipant
    {
        return $this->participant;
    }

    /**
     * @param EventParticipant $participant
     */
    public function setParticipant(?EventParticipant $participant): void
    {
        $this->participant = $participant;
    }

    /**
     * @return string
     */
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

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
