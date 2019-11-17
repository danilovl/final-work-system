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
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use FinalWork\FinalWorkBundle\Constant\TranslationConstant;
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    ConstantAwareTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};

/**
 * @ORM\Table(name="conversation_message_status_type")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="cache_long_time")
 * @Gedmo\Loggable
 */
class ConversationMessageStatusType
{
    use IdTrait;
    use SimpleInformationTrait;
    use ConstantAwareTrait;
    use CreateUpdateAbleTrait;

    /**
     * @var Collection|ConversationMessageStatus[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\ConversationMessageStatus", mappedBy="type")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $conversationMessageStatus;

    /**
     * ConversationType constructor.
     */
    public function __construct()
    {
        $this->conversationMessageStatus = new ArrayCollection;
    }

    /**
     * @return Collection|ConversationMessageStatus[]
     */
    public function getConversationMessageStatus(): Collection
    {
        return $this->conversationMessageStatus;
    }

    /**
     * @param Collection $conversation
     */
    public function setConversationMessageStatus(Collection $conversation): void
    {
        $this->conversationMessageStatus = $conversation;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
