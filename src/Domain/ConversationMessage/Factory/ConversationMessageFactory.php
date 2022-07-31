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

namespace App\Domain\ConversationMessage\Factory;

use App\Application\Factory\Model\BaseModelFactory;
use App\Domain\ConversationMessage\ConversationMessageModel;
use App\Domain\ConversationMessage\Entity\ConversationMessage;

class ConversationMessageFactory extends BaseModelFactory
{
    public function flushFromModel(
        ConversationMessageModel $conversationMessageModel,
        ?ConversationMessage $conversationMessage = null
    ): ConversationMessage {
        $conversationMessage = $conversationMessage ?? new ConversationMessage;
        $conversationMessage = $this->fromModel($conversationMessage, $conversationMessageModel);

        $this->entityManagerService->persistAndFlush($conversationMessage);

        return $conversationMessage;
    }

    public function fromModel(
        ConversationMessage $conversationMessage,
        ConversationMessageModel $conversationMessageModel
    ): ConversationMessage {
        $conversationMessage->setConversation($conversationMessageModel->conversation);
        $conversationMessage->setContent($conversationMessageModel->content);
        $conversationMessage->setOwner($conversationMessageModel->owner);
        $conversationMessage->setStatuses($conversationMessageModel->statuses);

        return $conversationMessage;
    }
}
