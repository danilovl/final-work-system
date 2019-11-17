<?php declare(strict_types=1);

/**
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace FinalWork\FinalWorkBundle\Model\ConversationMessage;

use Doctrine\Common\Collections\ArrayCollection;
use FinalWork\FinalWorkBundle\Entity\ConversationMessage;
use FinalWork\FinalWorkBundle\Model\Traits\{
    OwnerAwareTrait,
    ContentAwareTrait
};
use Symfony\Component\Validator\Constraints as Assert;

class ConversationMessageModel
{
    use ContentAwareTrait;
    use OwnerAwareTrait;

    /**
     * @Assert\NotBlank()
     */
    public $conversation;

    /**
     * @Assert\NotBlank()
     */
    public $status;

    /**
     * ConversationMessageModel constructor.
     */
    public function __construct()
    {
        $this->status = new ArrayCollection();
    }

    /**
     * @param ConversationMessage $conversationMessage
     * @return ConversationMessageModel
     */
    public static function fromConversationMessage(ConversationMessage $conversationMessage): self
    {
        $model = new self();
        $model->conversation = $conversationMessage->getConversation();
        $model->content = $conversationMessage->getContent();
        $model->owner = $conversationMessage->getOwner();
        $model->status = $conversationMessage->getStatus();

        return $model;
    }
}