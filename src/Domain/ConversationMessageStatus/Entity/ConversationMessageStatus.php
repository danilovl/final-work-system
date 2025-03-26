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

namespace App\Domain\ConversationMessageStatus\Entity;

use App\Application\Traits\Entity\{
    IdTrait,
    CreateUpdateAbleTrait
};
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationMessageStatus\Repository\ConversationMessageStatusRepository;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Domain\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'conversation_message_status')]
#[ORM\UniqueConstraint(name: 'conversation_message_status', columns: ['conversation_id', 'conversation_message_id', 'user_id'])]
#[ORM\Entity(repositoryClass: ConversationMessageStatusRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
#[Gedmo\Loggable]
class ConversationMessageStatus
{
    use IdTrait;
    use CreateUpdateAbleTrait;

    #[ORM\ManyToOne(targetEntity: Conversation::class, fetch: 'EAGER', inversedBy: 'statuses')]
    #[ORM\JoinColumn(name: 'conversation_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?Conversation $conversation = null;

    #[ORM\ManyToOne(targetEntity: ConversationMessage::class, fetch: 'EAGER', inversedBy: 'statuses')]
    #[ORM\JoinColumn(name: 'conversation_message_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?ConversationMessage $message = null;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER', inversedBy: 'conversationMessageStatus')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: ConversationMessageStatusType::class, fetch: 'EAGER', inversedBy: 'conversationMessageStatus')]
    #[ORM\JoinColumn(name: 'conversation_message_status_type_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ConversationMessageStatusType $type;

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(Conversation $conversation): void
    {
        $this->conversation = $conversation;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getMessage(): ?ConversationMessage
    {
        return $this->message;
    }

    public function setMessage(ConversationMessage $message): void
    {
        $this->message = $message;
    }

    public function getType(): ConversationMessageStatusType
    {
        return $this->type;
    }

    public function setType(ConversationMessageStatusType $type): void
    {
        $this->type = $type;
    }

    public function __toString(): string
    {
        return $this->getType()->getName();
    }
}
