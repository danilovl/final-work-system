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
use App\Domain\Conversation\DTO\Api\Output\ConversationListOutput;
use App\Domain\Conversation\DTO\Api\ConversationDTO;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationMessage\DTO\Api\Output\ConversationMessageListOutput;
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
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpKernel\Attribute\{
    MapQueryParameter,
    MapRequestPayload
};

#[OA\Tag(name: 'Conversation')]
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

    #[OA\Get(
        path: '/api/key/conversations/',
        description: 'Retrieves paginated list of conversations for current user.',
        summary: 'List conversations'
    )]
    #[OA\Parameter(
        name: 'search',
        description: 'Search term to filter conversations',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'Page number (starts from 1)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Items per page',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 20)
    )]
    #[OA\Response(
        response: 200,
        description: 'Paginated conversations list',
        content: new OA\JsonContent(ref: new Model(type: ConversationListOutput::class))
    )]
    public function list(
        Request $request,
        #[MapQueryParameter] ?string $search = null
    ): JsonResponse {
        return $this->conversationListHandle->__invoke($request, $search);
    }

    #[OA\Get(
        path: '/api/key/conversations/{id}',
        description: 'Retrieves conversation details for the given ID.',
        summary: 'Conversation detail'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Conversation ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: 'Conversation detail',
        content: new OA\JsonContent(ref: new Model(type: ConversationDTO::class))
    )]
    public function detail(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $conversation);

        return $this->conversationDetailHandle->__invoke($request, $conversation);
    }

    #[OA\Get(
        path: '/api/key/conversations/{id}/messages',
        description: 'Retrieves paginated list of messages for a conversation.',
        summary: 'List conversation messages'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Conversation ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
    )]
    #[OA\Parameter(
        name: 'search',
        description: 'Search term to filter messages within the conversation',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'Page number (starts from 1)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Items per page',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 20)
    )]
    #[OA\Response(
        response: 200,
        description: 'Paginated conversation messages list',
        content: new OA\JsonContent(ref: new Model(type: ConversationMessageListOutput::class))
    )]
    public function messages(
        Request $request,
        Conversation $conversation,
        #[MapQueryParameter] ?string $search = null
    ): JsonResponse {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $conversation);

        return $this->conversationMessageListHandle->__invoke($request, $conversation, $search);
    }

    #[OA\Post(
        path: '/api/key/conversations/{id}/message',
        description: 'Create a new message in the conversation.',
        summary: 'Create conversation message'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Conversation ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: ConversationMessageInput::class))
    )]
    #[OA\Response(
        response: 201,
        description: 'Message created'
    )]
    public function createMessage(
        Conversation $conversation,
        #[MapRequestPayload] ConversationMessageInput $conversationMessageInput,
    ): JsonResponse {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $conversation);

        return $this->conversationCreateMessageHandle->__invoke($conversation, $conversationMessageInput);
    }

    #[OA\Get(
        path: '/api/key/conversations/works/{id}/messages',
        description: 'Retrieves paginated list of messages related to the specific work for the current user.',
        summary: 'List work messages'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Work ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'Page number (starts from 1)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Items per page',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 20)
    )]
    #[OA\Response(
        response: 200,
        description: 'Paginated work messages list',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'count', type: 'integer', example: 10),
                new OA\Property(property: 'totalCount', type: 'integer', example: 42),
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(
                    property: 'result',
                    type: 'array',
                    items: new OA\Items(type: 'object')
                )
            ],
            type: 'object'
        )
    )]
    public function listWorkMessage(Request $request, Work $work): JsonResponse
    {
        return $this->conversationWorkMessageListHandle->__invoke($request, $work);
    }

    #[OA\Get(
        path: '/api/key/conversations/works/{id}',
        description: 'Retrieves conversation associated with the specified work for the current user.',
        summary: 'Work conversation'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Work ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: 'Work conversation detail',
        content: new OA\JsonContent(ref: new Model(type: ConversationDTO::class))
    )]
    public function conversationWork(Request $request, Work $work): JsonResponse
    {
        return $this->conversationWorkHandle->__invoke($request, $work);
    }

    #[OA\Put(
        path: '/api/key/conversations/{id_conversation}/messages/{id_message}/change/read/status',
        description: 'Change read status of a specific conversation message for the current user.',
        summary: 'Change message read status'
    )]
    #[OA\Parameter(
        name: 'id_conversation',
        description: 'Conversation ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
    )]
    #[OA\Parameter(
        name: 'id_message',
        description: 'Message ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: 'Message read status updated'
    )]
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
