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

use App\Constant\TranslationConstant;
use App\Entity\Traits\{
    IdTrait,
    IsReadTrait,
    IsOwnerTrait,
    CreateUpdateAbleTrait
};
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="conversation")
 * @ORM\Entity(repositoryClass="App\Repository\ConversationRepository")
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
     * @ORM\Column(name="name", type="string", nullable=true)
     * @Gedmo\Versioned
     */
    private ?string $name = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="conversationsOwner", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?User $owner = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ConversationMessage", mappedBy="conversation")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $messages = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Work", inversedBy="conversations", fetch="EAGER", fetch="EAGER")
     * @ORM\JoinColumn(name="work_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Work $work = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ConversationType", inversedBy="conversations", fetch="EAGER")
     * @ORM\JoinColumn(name="conversation_type_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?ConversationType $type = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ConversationMessageStatus", mappedBy="conversation")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $statuses = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ConversationParticipant", mappedBy="conversation")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $participants = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SystemEvent", mappedBy="conversation")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $systemEvents = null;

    private ?User $recipient = null;

    public function __construct()
    {
        $this->statuses = new ArrayCollection;
        $this->systemEvents = new ArrayCollection;
        $this->participants = new ArrayCollection;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

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

    public function setMessages(Collection $messages): void
    {
        $this->messages = $messages;
    }

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

    public function setParticipants(?Collection $participants): void
    {
        $this->participants = $participants;
    }

    public function getType(): ?ConversationType
    {
        return $this->type;
    }

    public function setType(ConversationType $type): void
    {
        $this->type = $type;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getWork(): ?Work
    {
        return $this->work;
    }

    public function setWork(?Work $work): void
    {
        $this->work = $work;
    }

    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

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

    public function setSystemEvents(Collection $systemEvents): void
    {
        $this->systemEvents = $systemEvents;
    }

    public function getTitle(): string
    {
        if ($this->getWork()) {
            return (string) $this->getWork();
        }

        return $this->getName() ?: TranslationConstant::EMPTY;
    }

    public function __toString(): string
    {
        return $this->getTitle();
    }
}
