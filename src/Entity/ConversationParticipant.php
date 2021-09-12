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

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Constant\TranslationConstant;
use App\Entity\Traits\IdTrait;

/**
 * @Gedmo\Loggable
 */
#[ORM\Table(name: 'conversation_participant')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
class ConversationParticipant
{
    use IdTrait;

    #[ORM\ManyToOne(targetEntity: Conversation::class, inversedBy: 'participants')]
    #[ORM\JoinColumn(name: 'conversation_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?Conversation $conversation = null;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER', inversedBy: 'conversationsParticipant')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?User $user = null;

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(Conversation $conversation): void
    {
        $this->conversation = $conversation;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function __toString(): string
    {
        return (string) $this->getUser() ?: TranslationConstant::EMPTY;
    }
}
