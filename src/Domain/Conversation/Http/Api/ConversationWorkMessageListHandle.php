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

use App\Domain\User\Service\UserService;
use App\Domain\Work\Entity\Work;
use App\Domain\Conversation\Service\ConversationStatusService;
use App\Domain\ConversationMessage\Factory\WorkConversationMessageDTOFactory;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};
use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Domain\ConversationMessage\Bus\Query\ConversationMessageQueryByWorkUser\{GetConversationMessageQueryByWorkUserQuery, GetConversationMessageQueryByWorkUserQueryResult};
use App\Infrastructure\Service\PaginatorService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Knp\Component\Pager\PaginatorInterface;

readonly class ConversationWorkMessageListHandle
{
    public function __construct(
        private UserService $userService,
        private QueryBusInterface $queryBus,
        private PaginatorService $paginatorService,
        private ParameterServiceInterface $parameterService,
        private WorkConversationMessageDTOFactory $workConversationMessageDTOFactory,
        private ConversationStatusService $conversationStatusService
    ) {}

    public function __invoke(Request $request, Work $work): JsonResponse
    {
        $user = $this->userService->getUser();

        $getConversationMessageQueryByWorkUserQuery = GetConversationMessageQueryByWorkUserQuery::create(
            work: $work,
            user: $user
        );
        /** @var GetConversationMessageQueryByWorkUserQueryResult $getConversationMessageQueryByWorkUserQueryResult */
        $getConversationMessageQueryByWorkUserQueryResult = $this->queryBus->handle($getConversationMessageQueryByWorkUserQuery);
        $query = $getConversationMessageQueryByWorkUserQueryResult->query;

        $page = $this->parameterService->getInt('pagination.work_detail_tab.message.page');
        $limit = $this->parameterService->getInt('pagination.work_detail_tab.message.limit');

        $pagination = $this->paginatorService->createPaginationRequest(
            request: $request,
            target: $query,
            page: $page,
            limit: $limit,
            options: [PaginatorInterface::DISTINCT => true]
        );

        $messages = [];
        foreach ($pagination as $message) {
            $isRead = $this->conversationStatusService->isConversationMessageRead($message, $user);

            $messages[] = $this->workConversationMessageDTOFactory->create(
                message: $message,
                isRead: $isRead
            );
        }

        return new JsonResponse([
            'count' => $pagination->count(),
            'totalCount' => $pagination->getTotalItemCount(),
            'success' => true,
            'result' => $messages
        ]);
    }
}
