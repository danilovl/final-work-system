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

namespace App\Domain\Conversation\Bus\Query\ConversationList;

use App\Domain\Conversation\Repository\Elastica\ElasticaConversationRepository;
use App\Infrastructure\Service\{
    PaginatorService,
    EntityManagerService
};
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Facade\ConversationFacade;
use App\Domain\ConversationType\Constant\ConversationTypeConstant;
use App\Domain\ConversationType\Entity\ConversationType;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\ConversationMessage\Bus\Query\LastByConversationIds\{GetLastByConversationIdsQuery, GetLastByConversationIdsQueryResult};
use App\Domain\ConversationMessage\Bus\Query\CountsByConversationIds\{GetCountsByConversationIdsQuery, GetCountsByConversationIdsQueryResult};
use App\Domain\ConversationMessage\Bus\Query\ConversationIdsByParticipantAndSearch\{GetConversationIdsByParticipantAndSearchQuery, GetConversationIdsByParticipantAndSearchQueryResult};

#[AsMessageHandler]
readonly class GetConversationListQueryHandler
{
    public function __construct(
        private ConversationFacade $conversationFacade,
        private QueryBusInterface $queryBus,
        private PaginatorService $paginatorService,
        private ElasticaConversationRepository $elasticaConversationRepository,
        private EntityManagerService $entityManagerService,
        private ParameterServiceInterface $parameterService
    ) {}

    public function __invoke(GetConversationListQuery $query): GetConversationListQueryResult
    {
        $user = $query->user;
        $type = $query->request->query->getString('type');
        $typeId = ConversationTypeConstant::tryIdByType($type);
        $types = [];

        if ($typeId !== null) {
            $types[] = $this->entityManagerService->getReference(ConversationType::class, $typeId);
        }

        $conversationsQuery = $this->conversationFacade->queryAllByParticipantUserTypes($user, $types);

        if ($query->search) {
            $getConversationIdsByParticipantAndSearchQuery = GetConversationIdsByParticipantAndSearchQuery::create(
                user: $user,
                search: $query->search
            );
            /** @var GetConversationIdsByParticipantAndSearchQueryResult $getConversationIdsByParticipantAndSearchQueryResult */
            $getConversationIdsByParticipantAndSearchQueryResult = $this->queryBus->handle($getConversationIdsByParticipantAndSearchQuery);
            $messageConversationIds = $getConversationIdsByParticipantAndSearchQueryResult->ids;

            $conversationIds = $this->elasticaConversationRepository
                ->getIdsByParticipantAndSearch($user, $messageConversationIds, $query->search);

            $conversationsQuery = $this->conversationFacade->queryAllByIds($conversationIds);
        }

        $conversationsQuery->setHydrationMode(Conversation::class);

        $pagination = $this->paginatorService->createPaginationRequest(
            request: $query->request,
            target: $conversationsQuery,
            page: $this->parameterService->getInt('pagination.default.page'),
            limit: $this->parameterService->getInt('pagination.default.limit'),
            options: ['wrap-queries' => true]
        );

        $conversationIds = [];
        /** @var Conversation $conversation */
        foreach ($pagination as $conversation) {
            $conversationIds[] = $conversation->getId();
        }

        $getCountsByConversationIdsQuery = GetCountsByConversationIdsQuery::create($conversationIds);
        /** @var GetCountsByConversationIdsQueryResult $getCountsByConversationIdsQueryResult */
        $getCountsByConversationIdsQueryResult = $this->queryBus->handle($getCountsByConversationIdsQuery);
        $messageCounts = $getCountsByConversationIdsQueryResult->counts;

        $getLastByConversationIdsQuery = GetLastByConversationIdsQuery::create($conversationIds);
        /** @var GetLastByConversationIdsQueryResult $getLastByConversationIdsQueryResult */
        $getLastByConversationIdsQueryResult = $this->queryBus->handle($getLastByConversationIdsQuery);
        $lastMessages = $getLastByConversationIdsQueryResult->messages;

        return new GetConversationListQueryResult(
            conversations: $pagination,
            messageCounts: $messageCounts,
            lastMessages: $lastMessages
        );
    }
}
