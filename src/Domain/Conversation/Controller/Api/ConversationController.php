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

namespace App\Domain\Conversation\Controller\Api;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\Conversation\DTO\Api\Input\ConversationMessageInput;
use App\Domain\Conversation\Entity\Conversation;
use App\Infrastructure\Service\AuthorizationCheckerService;
use App\Domain\Conversation\Http\Api\{
    ConversationListHandle,
    ConversationDetailHandle,
    ConversationMessageListHandle,
    ConversationWorkMessageListHandle,
    ConversationCreateMessageHandle
};
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse
};
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

readonly class ConversationController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private ConversationWorkMessageListHandle $conversionWorkHandle,
        private ConversationListHandle $conversationListHandle,
        private ConversationDetailHandle $conversationDetailHandle,
        private ConversationMessageListHandle $conversationMessageListHandle,
        private ConversationCreateMessageHandle $conversationCreateMessageHandle
    ) {}

    public function list(Request $request): JsonResponse
    {
        return $this->conversationListHandle->__invoke($request);
    }

    public function detail(Request $request, Conversation $conversation): Response
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $conversation);

        return $this->conversationDetailHandle->__invoke($request, $conversation);
    }

    public function messages(Request $request, Conversation $conversation): Response
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $conversation);

        return $this->conversationMessageListHandle->__invoke($request, $conversation);
    }

    public function createMessage(
        Conversation $conversation,
        #[MapRequestPayload] ConversationMessageInput $conversationMessageInput,
    ): Response
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $conversation);

        return $this->conversationCreateMessageHandle->__invoke($conversation, $conversationMessageInput);
    }

    public function listWorkMessage(Request $request, Work $work): Response
    {
        return $this->conversionWorkHandle->__invoke($request, $work);
    }
}
