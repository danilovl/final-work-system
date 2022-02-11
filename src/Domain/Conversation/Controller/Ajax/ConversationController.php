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

namespace App\Domain\Conversation\Controller\Ajax;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Http\Ajax\{
    ConversationLiveHandle,
    ConversationDeleteHandle,
    ConversationReadAllHandle,
    ConversationChangeReadMessageStatusHandle
};
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    StreamedResponse
};

class ConversationController extends AbstractController
{
    public function __construct(
        private ConversationChangeReadMessageStatusHandle $conversationChangeReadMessageStatusHandle,
        private ConversationReadAllHandle $conversationReadAllHandle,
        private ConversationDeleteHandle $conversationDeleteHandle,
        private ConversationLiveHandle $conversationLiveHandle
    ) {
    }

    public function changeReadMessageStatus(ConversationMessage $conversationMessage): JsonResponse
    {
        $this->denyAccessUnlessGranted(
            VoterSupportConstant::CHANGE_READ_MESSAGE_STATUS,
            $conversationMessage
        );

        return $this->conversationChangeReadMessageStatusHandle->handle($conversationMessage);
    }

    public function readAll(): JsonResponse
    {
        return $this->conversationReadAllHandle->handle();
    }

    public function delete(Conversation $conversation): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $conversation);

        return $this->conversationDeleteHandle->handle($conversation);
    }

    public function liveConversation(Conversation $conversation): StreamedResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $conversation);

        return $this->conversationLiveHandle->handle($conversation);
    }
}
