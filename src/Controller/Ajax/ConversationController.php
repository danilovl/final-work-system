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

use App\Constant\VoterSupportConstant;
use App\Controller\BaseController;
use App\Entity\{
    Conversation,
    ConversationMessage
};
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    StreamedResponse
};

class ConversationController extends BaseController
{
    public function changeReadMessageStatus(ConversationMessage $conversationMessage): JsonResponse
    {
        $this->denyAccessUnlessGranted(
            VoterSupportConstant::CHANGE_READ_MESSAGE_STATUS,
            $conversationMessage
        );

        return $this->get('app.http_handle_ajax.conversation.change_read_message_status')->handle($conversationMessage);
    }

    public function readAll(): JsonResponse
    {
        return $this->get('app.http_handle_ajax.conversation.read_all')->handle();
    }

    public function delete(Conversation $conversation): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $conversation);

        return $this->get('app.http_handle_ajax.conversation.delete')->handle($conversation);
    }

    public function liveConversation(Conversation $conversation): StreamedResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $conversation);

        return $this->get('app.http_handle_ajax.conversation.live')->handle($conversation);
    }
}
