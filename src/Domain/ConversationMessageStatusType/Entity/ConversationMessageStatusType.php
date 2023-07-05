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

namespace App\Domain\ConversationMessageStatusType\Entity;

use App\Application\Traits\Entity\{
    IdTrait,
    ConstantAwareTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};
use App\Domain\ConversationMessageStatus\Entity\ConversationMessageStatus;
use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection
};
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'conversation_message_status_type')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
#[Gedmo\Loggable]
class ConversationMessageStatusType
{
    use IdTrait;
    use SimpleInformationTrait;
    use ConstantAwareTrait;
    use CreateUpdateAbleTrait;

    #[ORM\OneToMany(mappedBy: 'type', targetEntity: ConversationMessageStatus::class)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $conversationMessageStatus;

    public function __construct()
    {
        $this->conversationMessageStatus = new ArrayCollection;
    }

    /**
     * @return Collection<ConversationMessageStatus>
     */
    public function getConversationMessageStatus(): Collection
    {
        return $this->conversationMessageStatus;
    }

    public function setConversationMessageStatus(Collection $conversation): void
    {
        $this->conversationMessageStatus = $conversation;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
