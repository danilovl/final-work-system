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
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Constant\TranslationConstant;
use App\Entity\Traits\{
    IdTrait,
    IsOwnerTrait,
    CreateUpdateAbleTrait
};

/**
 * @ORM\Table(name="event")
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\EventType", inversedBy="events", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_type_id", referencedColumnName="id", nullable=false)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?EventType $type = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="eventsOwner", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?User $owner = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EventAddress", inversedBy="events", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_address_id", referencedColumnName="id", nullable=true)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?EventAddress $address = null;

    /**
     * @ORM\Column(name="name", type="string", nullable=true)
     * @Gedmo\Versioned
     */
    private ?string $name = null;

    /**
     * @ORM\Column(name="start", type="datetime", nullable=false)
     * @Gedmo\Versioned
     */
    private ?DateTime $start = null;

    /**
     * @ORM\Column(name="end", type="datetime", nullable=false)
     * @Gedmo\Versioned
     */
    private ?DateTime $end = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\EventParticipant", mappedBy="event", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?EventParticipant $participant = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="event", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $comment = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SystemEvent", mappedBy="event")
     */
    private ?Collection $systemEvents = null;

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
