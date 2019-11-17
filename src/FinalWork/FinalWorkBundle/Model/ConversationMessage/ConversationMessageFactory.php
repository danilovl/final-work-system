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

use FinalWork\FinalWorkBundle\Model\BaseModelFactory;
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};
use FinalWork\FinalWorkBundle\Entity\ConversationMessage;

class ConversationMessageFactory extends BaseModelFactory
{
    /**
     * @param ConversationMessageModel $conversationMessageModel
     * @param ConversationMessage|null $conversationMessage
     * @return ConversationMessage
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function flushFromModel(
        ConversationMessageModel $conversationMessageModel,
        ?ConversationMessage $conversationMessage = null
    ): ConversationMessage {
        $conversationMessage = $conversationMessage ?? new ConversationMessage;
        $conversationMessage = $this->fromModel($conversationMessage, $conversationMessageModel);

        $this->em->persist($conversationMessage);
        $this->em->flush();

        return $conversationMessage;
    }

    /**
     * @param ConversationMessage $conversationMessage
     * @param ConversationMessageModel $conversationMessageModel
     * @return ConversationMessage
     */
    public function fromModel(
        ConversationMessage $conversationMessage,
        ConversationMessageModel $conversationMessageModel
    ): ConversationMessage {
        $conversationMessage->setConversation($conversationMessageModel->conversation);
        $conversationMessage->setContent($conversationMessageModel->content);
        $conversationMessage->setOwner($conversationMessageModel->owner);
        $conversationMessage->setStatus($conversationMessageModel->status);

        return $conversationMessage;
    }
}
