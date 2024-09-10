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
use App\Application\Service\AuthorizationCheckerService;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Http\Ajax\{
    ConversationLiveHandle,
    ConversationDeleteHandle,
    ConversationReadAllHandle,
    ConversationChangeReadMessageStatusHandle
};
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    StreamedResponse
};

readonly class ConversationController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private ConversationChangeReadMessageStatusHandle $conversationChangeReadMessageStatusHandle,
        private ConversationReadAllHandle $conversationReadAllHandle,
        private ConversationDeleteHandle $conversationDeleteHandle,
        private ConversationLiveHandle $conversationLiveHandle
    ) {}

    public function changeReadMessageStatus(ConversationMessage $conversationMessage): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(
            VoterSupportConstant::CHANGE_READ_MESSAGE_STATUS->value,
            $conversationMessage
        );

        return $this->conversationChangeReadMessageStatusHandle->__invoke($conversationMessage);
    }

    public function readAll(): JsonResponse
    {
        return $this->conversationReadAllHandle->__invoke();
    }

    public function delete(Conversation $conversation): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::DELETE->value, $conversation);

        return $this->conversationDeleteHandle->__invoke($conversation);
    }

    public function liveConversation(Conversation $conversation): StreamedResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $conversation);

        return $this->conversationLiveHandle->__invoke($conversation);
    }
}
