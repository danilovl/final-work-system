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

namespace App\Domain\ConversationMessage\Model;

use App\Application\Traits\Model\ContentAwareTrait;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\User\Traits\Model\OwnerAwareTrait;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};

class ConversationMessageModel
{
    use ContentAwareTrait;
    use OwnerAwareTrait;

    public ?int $id = null;

    public Conversation $conversation;

    public Collection $statuses;

    public function __construct()
    {
        $this->statuses = new ArrayCollection;
    }

    public static function fromConversationMessage(ConversationMessage $conversationMessage): self
    {
        $model = new self;
        $model->id = $conversationMessage->getId();
        $model->conversation = $conversationMessage->getConversation();
        $model->content = $conversationMessage->getContent();
        $model->owner = $conversationMessage->getOwner();
        $model->statuses = $conversationMessage->getStatuses();

        return $model;
    }
}
