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

use FinalWork\FinalWorkBundle\Constant\TranslationConstant;
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    IsReadTrait,
    IsOwnerTrait,
    CreateUpdateAbleTrait
};
use Doctrine\Common\Collections\{
    Criteria,
    Collection,
    ArrayCollection
};
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use FinalWork\SonataUserBundle\Entity\User;

/**
 * @ORM\Table(name="conversation")
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\ConversationRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
 * @Gedmo\Loggable
 */
class Conversation
{
    use IdTrait;
    use CreateUpdateAbleTrait;
    use IsOwnerTrait;
    use IsReadTrait;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", nullable=true)
     * @Gedmo\Versioned
     */
    private $name;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="conversationsOwner", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $owner;

    /**
     * @var Collection|ConversationMessage[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\ConversationMessage", mappedBy="conversation")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $messages;

    /**
     * @var Work|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\Work", inversedBy="conversations", fetch="EAGER", fetch="EAGER")
     * @ORM\JoinColumn(name="work_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $work;

    /**
     * @var ConversationType|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\ConversationType", inversedBy="conversations", fetch="EAGER")
     * @ORM\JoinColumn(name="conversation_type_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $type;

    /**
     * @var Collection|ConversationMessageStatus[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\ConversationMessageStatus", mappedBy="conversation")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $statuses;

    /**
     * @var Collection|ConversationParticipant[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\ConversationParticipant", mappedBy="conversation")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $participants;

    /**
     * @var Collection|SystemEvent[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\SystemEvent", mappedBy="conversation")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $systemEvents;

    /**
     * @var User|null
     */
    private $recipient;

    /**
     * Conversation constructor.
     */
    public function __construct()
    {
        $this->statuses = new ArrayCollection;
        $this->systemEvents = new ArrayCollection;
        $this->participants = new ArrayCollection;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
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
    public function setOwner(?User $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @return Collection|ConversationMessage[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    /**
     * @param Collection $messages
     */
    public function setMessages(Collection $messages): void
    {
        $this->messages = $messages;
    }

    /**
     * @param ConversationMessage $message
     */
    public function addMessage(ConversationMessage $message): void
    {
        $message->setConversation($this);
        $this->messages[] = $message;
    }

    /**
     * @return Collection|ConversationParticipant[]|null
     */
    public function getParticipants(): ?Collection
    {
        return $this->participants;
    }

    /**
     * @param Collection|null $participants
     */
    public function setParticipants(?Collection $participants): void
    {
        $this->participants = $participants;
    }

    /**
     * @return ConversationType|null
     */
    public function getType(): ?ConversationType
    {
        return $this->type;
    }

    /**
     * @param ConversationType $type
     */
    public function setType(ConversationType $type): void
    {
        $this->type = $type;
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
     * @return User|null
     */
    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    /**
     * @param User|null $recipient
     */
    public function setRecipient(?User $recipient): void
    {
        $this->recipient = $recipient;
    }

    /**
     * @return Collection|ConversationMessageStatus[]
     */
    public function getStatuses(): Collection
    {
        return $this->statuses;
    }

    /**
     * @param Collection $statuses
     */
    public function setStatuses(Collection $statuses): void
    {
        $this->statuses = $statuses;
    }

    /**
     * @return Collection|SystemEvent[]
     */
    public function getSystemEvents(): Collection
    {
        return $this->systemEvents;
    }

    /**
     * @param Collection $systemEvents
     */
    public function setSystemEvents(Collection $systemEvents): void
    {
        $this->systemEvents = $systemEvents;
    }

    /**
     * @return ConversationMessage|null
     */
    public function getLastMessage(): ?ConversationMessage
    {
        $messages = $this->getMessages();

        if ($messages->count() > 0) {
            $criteria = Criteria::create()->orderBy([
                'createdAt' => Criteria::DESC
            ])->setMaxResults(1);

            return $messages->matching($criteria)[0];
        }

        return null;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isParticipant(User $user): bool
    {
        $participants = $this->getParticipants();

        foreach ($participants as $participant) {
            if ($participant->getUser()->getId() === $user->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getParticipantIds(): array
    {
        $participantIds = [];
        $participants = $this->getParticipants();

        foreach ($participants as $participant) {
            $participantIds[] = $participant->getUser()->getId();
        }

        sort($participantIds);

        return $participantIds;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        if ($this->getWork()) {
            return (string)$this->getWork();
        }

        return $this->getName() ?: TranslationConstant::EMPTY;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getTitle();
    }
}
