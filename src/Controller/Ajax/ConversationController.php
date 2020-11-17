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

namespace App\Controller\Ajax;

use App\Constant\{
    AjaxJsonTypeConstant,
    ConversationMessageStatusTypeConstant,
    VoterSupportConstant
};
use App\Controller\BaseController;
use App\Entity\{
    Conversation,
    ConversationMessage,
    ConversationMessageStatusType
};
use Symfony\Component\HttpFoundation\JsonResponse;

class ConversationController extends BaseController
{
    public function changeReadMessageStatus(ConversationMessage $conversationMessage): JsonResponse
    {
        $this->denyAccessUnlessGranted(
            VoterSupportConstant::CHANGE_READ_MESSAGE_STATUS,
            $conversationMessage
        );

        $this->get('app.facade.conversation_message')
            ->changeReadMessageStatus($this->getUser(), $conversationMessage);

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }

    public function readAll(): JsonResponse
    {
        $user = $this->getUser();
        $isUnreadExist = $this->get('app.facade.conversation_message')
            ->isUnreadMessagesByRecipient($user);

        if ($isUnreadExist) {
            $this->get('app.facade.conversation_message_status')
                ->updateAllToStatus(
                    $user,
                    $this->getReference(
                        ConversationMessageStatusType::class,
                        ConversationMessageStatusTypeConstant::READ
                    )
                );
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }

    public function delete(Conversation $conversation): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $conversation);

        $this->removeEntity($conversation);

        return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
