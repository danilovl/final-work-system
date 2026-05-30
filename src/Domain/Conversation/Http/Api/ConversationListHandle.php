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

use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Application\Mapper\ObjectToDtoMapper;
use App\Domain\Conversation\Bus\Query\ConversationList\{
    GetConversationListQuery,
    GetConversationListQueryResult
};
use App\Domain\Conversation\DTO\Api\ConversationDTO;
use App\Domain\Conversation\DTO\Api\Output\ConversationListOutput;
use App\Domain\Conversation\Service\ConversationService;
use App\Domain\ConversationMessage\DTO\Api\ConversationMessageDTO;
use App\Domain\User\DTO\Api\UserDTO;
use App\Domain\User\Service\UserService;
use App\Domain\Work\DTO\Api\WorkDTO;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

readonly class ConversationListHandle
{
    public function __construct(
        private UserService $userService,
        private QueryBusInterface $queryBus,
        private ObjectToDtoMapper $objectToDtoMapper,
        private ConversationService $conversationService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->userService->getUser();

        $query = GetConversationListQuery::create(
            request: $request,
            user: $user,
            search: $request->query->get('search')
        );

        /** @var GetConversationListQueryResult $result */
        $result = $this->queryBus->handle($query);
        $pagination = $result->conversations;

        $conversations = [];
        foreach ($pagination as $conversation) {
            $lastMessage = $this->conversationService->getLastMessage($conversation);
            
            $lastMessageDto = null;
            if ($lastMessage !== null) {
                $ownerDto = $this->objectToDtoMapper->map($lastMessage->getOwner(), UserDTO::class);
              
                $lastMessageDto = new ConversationMessageDTO(
                    id: $lastMessage->getId(),
                    owner: $ownerDto,
                    createdAt: $lastMessage->getCreatedAt()->format(DateTimeInterface::ATOM)
                );
            }

            $recipientDto = null;
            if ($conversation->getRecipient() !== null) {
                $recipientDto = $this->objectToDtoMapper->map($conversation->getRecipient(), UserDTO::class);
            }

            $workDto = null;
            if ($conversation->getWork() !== null) {
                $workDto = $this->objectToDtoMapper->map($conversation->getWork(), WorkDTO::class);
            }
            
            $conversations[] = new ConversationDTO(
                id: $conversation->getId(),
                name: $conversation->getName(),
                isRead: $conversation->isRead(),
                recipient: $recipientDto,
                work: $workDto,
                lastMessage: $lastMessageDto
            );
        }

        $output = new ConversationListOutput(
            numItemsPerPage: $pagination->getItemNumberPerPage(),
            totalCount: $pagination->getTotalItemCount(),
            currentItemCount: $pagination->count(),
            result: $conversations
        );

        return new JsonResponse($output);
    }
}