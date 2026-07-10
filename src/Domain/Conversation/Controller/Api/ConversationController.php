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

use App\Application\Attribute\EntityRelationValidatorAttribute;
use App\Application\Constant\VoterSupportConstant;
use App\Domain\Conversation\DTO\Api\Input\ConversationMessageInput;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Infrastructure\Service\AuthorizationCheckerService;
use App\Domain\Conversation\Http\Api\{
    ConversationListHandle,
    ConversationDetailHandle,
    ConversationMessageListHandle,
    ConversationWorkMessageListHandle,
    ConversationWorkHandle,
    ConversationCreateMessageHandle,
    ConversationChangeMessageReadStatusHandle,
    ConversationChangeAllMessageToReadHandle
};
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpKernel\Attribute\{MapQueryParameter, MapRequestPayload};

readonly class ConversationController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private ConversationWorkMessageListHandle $conversationWorkMessageListHandle,
        private ConversationWorkHandle $conversationWorkHandle,
        private ConversationListHandle $conversationListHandle,
        private ConversationDetailHandle $conversationDetailHandle,
        private ConversationMessageListHandle $conversationMessageListHandle,
        private ConversationCreateMessageHandle $conversationCreateMessageHandle,
        private ConversationChangeMessageReadStatusHandle $conversationChangeMessageReadStatusHandle,
        private ConversationChangeAllMessageToReadHandle $conversationChangeAllMessageToReadHandle
    ) {}

    public function list(Request $request): JsonResponse
    {
        return $this->conversationListHandle->__invoke($request);
    }

    public function detail(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $conversation);

        return $this->conversationDetailHandle->__invoke($request, $conversation);
    }

    public function messages(
        Request $request,
        Conversation $conversation,
        #[MapQueryParameter] ?string $search = null
    ): JsonResponse {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $conversation);

        return $this->conversationMessageListHandle->__invoke($request, $conversation, $search);
    }

    public function createMessage(
        Conversation $conversation,
        #[MapRequestPayload] ConversationMessageInput $conversationMessageInput,
    ): JsonResponse {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $conversation);

        return $this->conversationCreateMessageHandle->__invoke($conversation, $conversationMessageInput);
    }

    public function listWorkMessage(Request $request, Work $work): JsonResponse
    {
        return $this->conversationWorkMessageListHandle->__invoke($request, $work);
    }

    public function conversationWork(Request $request, Work $work): JsonResponse
    {
        return $this->conversationWorkHandle->__invoke($request, $work);
    }

    #[EntityRelationValidatorAttribute(sourceEntity: ConversationMessage::class, targetEntity: Conversation::class)]
    public function changeMessageReadStatus(
        #[MapEntity(mapping: ['id_conversation' => 'id'])] Conversation $conversation,
        #[MapEntity(mapping: ['id_message' => 'id'])] ConversationMessage $conversationMessage
    ): JsonResponse {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $conversationMessage->getConversation());

        return $this->conversationChangeMessageReadStatusHandle->__invoke($conversationMessage);
    }

    public function allMessageToRead(): JsonResponse
    {
        return $this->conversationChangeAllMessageToReadHandle->__invoke();
    }
}
