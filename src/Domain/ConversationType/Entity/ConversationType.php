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

namespace App\Domain\ConversationType\Entity;

use App\Application\Traits\Entity\{
    IdTrait,
    ConstantAwareTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};
use App\Domain\Conversation\Entity\Conversation;
use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection
};
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'conversation_type')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
#[Gedmo\Loggable]
class ConversationType
{
    use IdTrait;
    use SimpleInformationTrait;
    use ConstantAwareTrait;
    use CreateUpdateAbleTrait;

    #[ORM\OneToMany(mappedBy: 'type', targetEntity: Conversation::class)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $conversations;

    public function __construct()
    {
        $this->conversations = new ArrayCollection;
    }

    /**
     * @return Collection<Conversation>
     */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function setConversations(Collection $conversations): void
    {
        $this->conversations = $conversations;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
