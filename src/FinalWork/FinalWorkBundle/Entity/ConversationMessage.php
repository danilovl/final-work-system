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
    IsReadTrait,
    IsOwnerTrait,
    CreateUpdateAbleTrait
};

/**
 * @ORM\Table(name="conversation_message")
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\ConversationMessageRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
 * @Gedmo\Loggable
 */
class ConversationMessage
{
    use IdTrait;
    use CreateUpdateAbleTrait;
    use IsOwnerTrait;
    use IsReadTrait;

    /**
     * @var Conversation|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\Conversation", inversedBy="messages")
     * @ORM\JoinColumn(name="conversation_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $conversation;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="conversationMessages", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $owner;

    /**
     * @var Collection|ConversationMessageStatus[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\ConversationMessageStatus", mappedBy="message")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $status;

    /**
     * @var string|null
     *
     * @ORM\Column(name="content", type="text", nullable=false)
     * @Gedmo\VersioncreateConversationMessageStatused
     */
    private $content;

    /**
     * ConversationMessage constructor.
     */
    public function __construct()
    {
        $this->status = new ArrayCollection;
    }

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
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return Collection|ConversationMessageStatus|[]
     */
    public function getStatus(): ?Collection
    {
        return $this->status;
    }

    /**
     * @param Collection $status
     */
    public function setStatus(Collection $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getContent() ?: TranslationConstant::EMPTY;
    }
}
