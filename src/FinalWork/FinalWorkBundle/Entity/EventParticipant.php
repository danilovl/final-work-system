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
use FinalWork\FinalWorkBundle\Constant\TranslationConstant;
use FinalWork\SonataUserBundle\Entity\User;
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    CreateUpdateAbleTrait
};

/**
 * @ORM\Table(name="event_participant")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
 * @Gedmo\Loggable
 */
class EventParticipant
{
    use IdTrait;
    use CreateUpdateAbleTrait;

    /**
     * @var string|null
     *
     * @ORM\Column(name="first_name", type="string", nullable=true))
     * @Gedmo\Versioned
     */
    private $firstName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="second_name", type="string", type="string", nullable=true))
     * @Gedmo\Versioned
     */
    private $secondName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email", type="string", type="string", nullable=true))
     * @Gedmo\Versioned
     */
    private $email;

    /**
     * @var Event
     *
     * @ORM\OneToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\Event", inversedBy="participant")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id",  nullable=true, onDelete="CASCADE")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $event;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="eventsParticipant")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $user;

    /**
     * @var Work|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\Work", inversedBy="eventParticipants")
     * @ORM\JoinColumn(name="work_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $work;

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string|null
     */
    public function getSecondName(): ?string
    {
        return $this->secondName;
    }

    /**
     * @param string $secondName
     */
    public function setSecondName(?string $secondName): void
    {
        $this->secondName = $secondName;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return Event|null
     */
    public function getEvent(): ?Event
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    /**
     * @return Work|null
     */
    public function getWork(): ?Work
    {
        return $this->work;
    }

    /**
     * @param Work $work
     */
    public function setWork(?Work $work): void
    {
        $this->work = $work;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        $participant = null;

        if ($this->getUser()) {
            $participant = $this->getUser()->getFullNameDegree();
        }

        if ($this->getWork()) {
            if ($this->getUser()) {
                $participant .= sprintf(' | %s', $this->getWork()->getTitle());
            } else {
                $participant = $this->getWork()->getTitle();

            }
        }

        if ($participant === null &&
            $this->getFirstName() &&
            $this->getSecondName() &&
            $this->getEmail()
        ) {
            $participant = sprintf('%s %s | %s',
                $this->getFirstName(),
                $this->getSecondName(),
                $this->getEmail()
            );
        }

        return $participant ?: TranslationConstant::EMPTY;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
