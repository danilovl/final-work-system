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
use App\Domain\ConversationMessage\Repository\Elastica\ElasticaConversationMessageRepository;
use App\Infrastructure\Service\{
    PaginatorService,
    EntityManagerService
};
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Facade\ConversationFacade;
use App\Domain\Conversation\Helper\ConversationHelper;
use App\Domain\ConversationType\Constant\ConversationTypeConstant;
use App\Domain\ConversationType\Entity\ConversationType;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetConversationListQueryHandler
{
    public function __construct(
        private ConversationFacade $conversationFacade,
        private PaginatorService $paginatorService,
        private ElasticaConversationRepository $elasticaConversationRepository,
        private ElasticaConversationMessageRepository $elasticaConversationMessageRepository,
        private EntityManagerService $entityManagerService,
        private ParameterServiceInterface $parameterService
    ) {}

    public function __invoke(GetConversationListQuery $query): GetConversationListQueryResult
    {
        $user = $query->user;
        $type = $query->request->query->get('type');
        $types = [];

        if (!empty($type)) {
            $typeId = ConversationTypeConstant::getIdByType($type);
            $types[] = $this->entityManagerService->getReference(ConversationType::class, $typeId);
        }

        $conversationsQuery = $this->conversationFacade->queryAllByParticipantUserTypes($user, $types);

        if ($query->search) {
            $messageIds = $this->elasticaConversationMessageRepository
                ->getMessageIdsByParticipantAndSearch($user, $query->search);

            $conversationIds = $this->elasticaConversationRepository
                ->getIdsByParticipantAndSearch($user, $messageIds, $query->search);

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

        $this->conversationFacade->setIsReadToConversations($pagination, $user);
        ConversationHelper::getConversationOpposite($pagination, $user);

        return new GetConversationListQueryResult($pagination);
    }
}
