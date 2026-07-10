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

use App\Application\Mapper\ObjectToDtoMapper;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Facade\ConversationMessageFacade;
use App\Domain\ConversationMessage\DTO\Api\ConversationMessageDetailDTO;
use App\Domain\ConversationMessage\DTO\Api\Output\ConversationMessageListOutput;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationMessage\Repository\Elastica\ElasticaConversationMessageRepository;
use App\Domain\User\DTO\Api\UserDTO;
use App\Domain\User\Service\UserService;
use App\Infrastructure\Service\PaginatorService;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

readonly class ConversationMessageListHandle
{
    public function __construct(
        private UserService $userService,
        private ConversationMessageFacade $conversationMessageFacade,
        private ObjectToDtoMapper $objectToDtoMapper,
        private PaginatorService $paginatorService,
        private ElasticaConversationMessageRepository $elasticaConversationMessageRepository
    ) {}

    public function __invoke(Request $request, Conversation $conversation, ?string $search = null): JsonResponse
    {
        $user = $this->userService->getUser();

        $conversationMessagesQuery = $this->conversationMessageFacade
            ->queryMessagesByConversation($conversation);

        $search ??= '';
        if (!empty(mb_trim($search))) {
            $conversationMessageIds = $this->elasticaConversationMessageRepository
                ->getMessageIdsByConversationAndSearch($conversation, $search);

            $conversationMessagesQuery = $this->conversationMessageFacade->queryByIds($conversationMessageIds);
        }

        $conversationMessagesQuery->setHydrationMode(ConversationMessage::class);

        $pagination = $this->paginatorService
            ->createPaginationRequest($request, $conversationMessagesQuery);

        $this->conversationMessageFacade->setIsReadToConversationMessages($pagination, $user);

        $messages = [];
        /** @var ConversationMessage $message */
        foreach ($pagination as $message) {
            $ownerDto = $this->objectToDtoMapper->map($message->getOwner(), UserDTO::class);

            $messages[] = new ConversationMessageDetailDTO(
                id: $message->getId(),
                owner: $ownerDto,
                content: $message->getContent(),
                isRead: $message->isRead(),
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
