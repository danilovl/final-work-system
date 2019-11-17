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
use Doctrine\ORM\Mapping\UniqueConstraint;
use FinalWork\FinalWorkBundle\Constant\TranslationConstant;
use FinalWork\SonataUserBundle\Entity\User;
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    CreateUpdateAbleTrait
};

/**
 * @ORM\Table(name="conversation_message_status",
 *     uniqueConstraints={
 *     @UniqueConstraint(name="conversation_message_status", columns={"conversation_id", "conversation_message_id", "user_id"})
 * })
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\ConversationMessageStatusRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
 * @Gedmo\Loggable
 */
class ConversationMessageStatus
{
    use IdTrait;
    use CreateUpdateAbleTrait;

    /**
     * @var Conversation|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\Conversation", inversedBy="statuses", fetch="EAGER")
     * @ORM\JoinColumn(name="conversation_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $conversation;

    /**
     * @var ConversationMessage|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\ConversationMessage", inversedBy="status", fetch="EAGER")
     * @ORM\JoinColumn(name="conversation_message_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $message;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="conversationMessageStatus", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $user;

    /**
     * @var ConversationMessageStatusType|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\ConversationMessageStatusType", inversedBy="conversationMessageStatus", fetch="EAGER")
     * @ORM\JoinColumn(name="conversation_message_status_type_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $type;

    /**
     * @return Conversation|null
     */
    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    /**
     * @param Conversation $conversation
     */
    public function setConversation(Conversation $conversation): void
    {
        $this->conversation = $conversation;
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
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return ConversationMessage|null
     */
    public function getMessage(): ?ConversationMessage
    {
        return $this->message;
    }

    /**
     * @param ConversationMessage $message
     */
    public function setMessage(ConversationMessage $message): void
    {
        $this->message = $message;
    }

    /**
     * @return ConversationMessageStatusType|null
     */
    public function getType(): ?ConversationMessageStatusType
    {
        return $this->type;
    }

    /**
     * @param ConversationMessageStatusType $type
     */
    public function setType(ConversationMessageStatusType $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getType()->getName() ?: TranslationConstant::EMPTY;
    }
}
