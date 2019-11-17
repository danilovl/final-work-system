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
use FinalWork\FinalWorkBundle\Entity\Traits\IdTrait;
use FinalWork\SonataUserBundle\Entity\User;

/**
 * @ORM\Table(name="conversation_participant")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
 * @Gedmo\Loggable
 */
class ConversationParticipant
{
    use IdTrait;

    /**
     * @var Conversation|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\Conversation", inversedBy="participants")
     * @ORM\JoinColumn(name="conversation_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $conversation;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="conversationsParticipant", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $user;

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
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->getUser() ?: TranslationConstant::EMPTY;
    }
}
