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

namespace App\Domain\Conversation\Http\Api;

use Danilovl\ObjectDtoMapper\Service\ObjectToDtoMapperInterface;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Service\ConversationStatusService;
use App\Domain\ConversationMessage\DTO\Api\ConversationMessageDetailDTO;
use App\Domain\ConversationMessage\DTO\Api\Output\ConversationMessageListOutput;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\User\DTO\Api\UserDTO;
use App\Domain\User\Service\UserService;
use App\Infrastructure\Service\PaginatorService;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Domain\ConversationMessage\Bus\Query\ConversationMessageQueryByIds\{GetConversationMessageQueryByIdsQuery, GetConversationMessageQueryByIdsQueryResult};
use App\Domain\ConversationMessage\Bus\Query\MessageIdsByConversationAndSearch\{GetMessageIdsByConversationAndSearchQuery, GetMessageIdsByConversationAndSearchQueryResult};
use App\Domain\ConversationMessage\Bus\Query\ConversationMessageQueryByConversation\{GetConversationMessageQueryByConversationQuery, GetConversationMessageQueryByConversationQueryResult};

readonly class ConversationMessageListHandle
{
    public function __construct(
        private UserService $userService,
        private QueryBusInterface $queryBus,
        private ObjectToDtoMapperInterface $objectToDtoMapper,
        private PaginatorService $paginatorService,
        private ConversationStatusService $conversationStatusService
    ) {}

    public function __invoke(Request $request, Conversation $conversation, ?string $search = null): JsonResponse
    {
        $user = $this->userService->getUser();

        $getConversationMessageQueryByConversationQuery = GetConversationMessageQueryByConversationQuery::create($conversation);
        /** @var GetConversationMessageQueryByConversationQueryResult $getConversationMessageQueryByConversationQueryResult */
        $getConversationMessageQueryByConversationQueryResult = $this->queryBus->handle($getConversationMessageQueryByConversationQuery);
        $conversationMessagesQuery = $getConversationMessageQueryByConversationQueryResult->query;

        $search ??= '';
        if (!empty(mb_trim($search))) {
            $getMessageIdsByConversationAndSearchQuery = GetMessageIdsByConversationAndSearchQuery::create(
                conversation: $conversation,
                search: $search
            );
            /** @var GetMessageIdsByConversationAndSearchQueryResult $getMessageIdsByConversationAndSearchQueryResult */
            $getMessageIdsByConversationAndSearchQueryResult = $this->queryBus->handle($getMessageIdsByConversationAndSearchQuery);
            $conversationMessageIds = $getMessageIdsByConversationAndSearchQueryResult->ids;

            $getConversationMessageQueryByIdsQuery = GetConversationMessageQueryByIdsQuery::create($conversationMessageIds);
            /** @var GetConversationMessageQueryByIdsQueryResult $getConversationMessageQueryByIdsQueryResult */
            $getConversationMessageQueryByIdsQueryResult = $this->queryBus->handle($getConversationMessageQueryByIdsQuery);
            $conversationMessagesQuery = $getConversationMessageQueryByIdsQueryResult->query;
        }

        $conversationMessagesQuery->setHydrationMode(ConversationMessage::class);

        $pagination = $this->paginatorService
            ->createPaginationRequest($request, $conversationMessagesQuery);

        $messages = [];
        /** @var ConversationMessage $message */
        foreach ($pagination as $message) {
            $ownerDto = $this->objectToDtoMapper->map($message->getOwner(), UserDTO::class);
            $isRead = $this->conversationStatusService->isConversationMessageRead($message, $user);

            $messages[] = new ConversationMessageDetailDTO(
                id: $message->getId(),
                owner: $ownerDto,
                content: $message->getContent(),
                isRead: $isRead,
                createdAt: $message->getCreatedAt()->format(DateTimeInterface::ATOM)
            );
        }

        $output = new ConversationMessageListOutput(
            numItemsPerPage: $pagination->getItemNumberPerPage(),
            totalCount: $pagination->getTotalItemCount(),
            currentItemCount: $pagination->count(),
            result: $messages
        );

        return new JsonResponse($output);
    }
}
