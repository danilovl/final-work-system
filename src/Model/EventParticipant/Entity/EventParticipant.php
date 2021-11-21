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

namespace App\Model\EventParticipant\Entity;

use App\Model\Event\Entity\Event;
use App\Model\User\Entity\User;
use App\Model\Work\Entity\Work;
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
#[ORM\Table(name: 'event_participant')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
class EventParticipant
{
    use IdTrait;
    use CreateUpdateAbleTrait;

    /**
     * @Gedmo\Versioned
     */
    #[ORM\Column(name: 'first_name', type: Types::STRING, nullable: true)]
    private ?string $firstName = null;

    /**
     * @Gedmo\Versioned
     */
    #[ORM\Column(name: 'second_name', type: Types::STRING, nullable: true)]
    private ?string $secondName = null;

    /**
     * @Gedmo\Versioned
     */
    #[ORM\Column(name: 'email', type: Types::STRING, nullable: true)]
    private ?string $email = null;

    #[ORM\OneToOne(inversedBy: 'participant', targetEntity: Event::class)]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?Event $event = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'eventsParticipant')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Work::class, inversedBy: 'eventParticipants')]
    #[ORM\JoinColumn(name: 'work_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?Work $work = null;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getSecondName(): ?string
    {
        return $this->secondName;
    }

    public function setSecondName(?string $secondName): void
    {
        $this->secondName = $secondName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    public function getWork(): ?Work
    {
        return $this->work;
    }

    public function setWork(?Work $work): void
    {
        $this->work = $work;
    }

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

    public function __toString(): string
    {
        return $this->toString();
    }
}
