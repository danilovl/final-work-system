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

namespace App\Domain\ConversationMessage\Entity;

use App\Application\Constant\TranslationConstant;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\User\Entity\User;
use App\Application\Traits\Entity\{
    IdTrait,
    IsReadTrait,
    IsOwnerTrait,
    CreateUpdateAbleTrait
};
use App\Domain\ConversationMessage\Repository\ConversationMessageRepository;
use App\Domain\ConversationMessageStatus\Entity\ConversationMessageStatus;
use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection
};
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'conversation_message')]
#[ORM\Entity(repositoryClass: ConversationMessageRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
#[Gedmo\Loggable]
class ConversationMessage
{
    use IdTrait;
    use CreateUpdateAbleTrait;
    use IsOwnerTrait;
    use IsReadTrait;

    #[ORM\ManyToOne(targetEntity: Conversation::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(name: 'conversation_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?Conversation $conversation = null;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER', inversedBy: 'conversationMessages')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?User $owner = null;

    #[ORM\OneToMany(mappedBy: 'message', targetEntity: ConversationMessageStatus::class)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $statuses;

    #[ORM\Column(name: 'content', type: Types::TEXT, nullable: false)]
    #[Gedmo\Versioned]
    private ?string $content = null;

    public function __construct()
    {
        $this->statuses = new ArrayCollection;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(Conversation $conversation): void
    {
        $this->conversation = $conversation;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
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

    public function __toString(): string
    {
        return $this->getContent() ?: TranslationConstant::EMPTY;
    }
}
