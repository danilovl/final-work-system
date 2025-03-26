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

namespace App\Domain\Conversation\Entity;

use App\Application\Constant\TranslationConstant;
use App\Application\Exception\PropertyValueIsNullException;
use App\Application\Helper\ArrayMapHelper;
use App\Domain\Work\Entity\Work;
use App\Application\Traits\Entity\{
    IdTrait,
    IsReadTrait,
    IsOwnerTrait,
    CreateUpdateAbleTrait
};
use App\Domain\Conversation\Repository\ConversationRepository;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationMessageStatus\Entity\ConversationMessageStatus;
use App\Domain\ConversationParticipant\Entity\ConversationParticipant;
use App\Domain\ConversationType\Entity\ConversationType;
use App\Domain\SystemEvent\Entity\SystemEvent;
use App\Domain\User\Entity\User;
use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection
};
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Webmozart\Assert\Assert;

#[ORM\Table(name: 'conversation')]
#[ORM\Entity(repositoryClass: ConversationRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
#[Gedmo\Loggable]
class Conversation
{
    use IdTrait;
    use CreateUpdateAbleTrait;
    use IsOwnerTrait;
    use IsReadTrait;

    #[ORM\Column(name: 'name', type: Types::STRING, nullable: true)]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER', inversedBy: 'conversationsOwner')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private User $owner;

    /** @var Collection<ConversationMessage> */
    #[ORM\OneToMany(mappedBy: 'conversation', targetEntity: ConversationMessage::class)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $messages;

    #[ORM\ManyToOne(targetEntity: Work::class, fetch: 'EAGER', inversedBy: 'conversations')]
    #[ORM\JoinColumn(name: 'work_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?Work $work = null;

    #[ORM\ManyToOne(targetEntity: ConversationType::class, fetch: 'EAGER', inversedBy: 'conversations')]
    #[ORM\JoinColumn(name: 'conversation_type_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ConversationType $type;

    /** @var Collection<ConversationMessageStatus> */
    #[ORM\OneToMany(mappedBy: 'conversation', targetEntity: ConversationMessageStatus::class)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $statuses;

    /** @var Collection<ConversationParticipant> */
    #[ORM\OneToMany(mappedBy: 'conversation', targetEntity: ConversationParticipant::class)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $participants;

    /** @var Collection<SystemEvent> */
    #[ORM\OneToMany(mappedBy: 'conversation', targetEntity: SystemEvent::class)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $systemEvents;

    private ?User $recipient = null;

    public function __construct()
    {
        $this->messages = new ArrayCollection;
        $this->statuses = new ArrayCollection;
        $this->systemEvents = new ArrayCollection;
        $this->participants = new ArrayCollection;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @return Collection<ConversationMessage>
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
     * @return Collection<ConversationParticipant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    /**
     * @param User[] $users
     * @return Collection<ConversationParticipant>
     */
    public function getParticipantsExceptUsers(array $users): Collection
    {
        Assert::allIsInstanceOf($users, User::class);

        $ids = ArrayMapHelper::getObjectsIds($users);

        /** @var Collection<ConversationParticipant> $participants */
        $participants = $this->participants->filter(static function (ConversationParticipant $participant) use ($ids): bool {
            return !in_array($participant->getUser()->getId(), $ids, true);
        });

        return $participants;
    }

    public function setParticipants(Collection $participants): void
    {
        $this->participants = $participants;
    }

    public function getType(): ConversationType
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

    public function getRecipientMust(): User
    {
        if (!$this->recipient) {
            throw new PropertyValueIsNullException('Recipient is null.');
        }

        return $this->recipient;
    }

    public function setRecipient(User $recipient): void
    {
        $this->recipient = $recipient;
    }

    /**
     * @return Collection<ConversationMessageStatus>
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
     * @return Collection<SystemEvent>
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

        return $this->getName() ?: TranslationConstant::EMPTY->value;
    }

    public function __toString(): string
    {
        return $this->getTitle();
    }
}
