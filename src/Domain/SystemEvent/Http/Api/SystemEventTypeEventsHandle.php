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

namespace App\Domain\SystemEvent\Http\Api;

use App\Infrastructure\Constant\DateFormatConstant;
use App\Infrastructure\Exception\InvalidArgumentException;
use App\Domain\SystemEvent\Constant\SystemEventStatusConstant;
use App\Domain\SystemEvent\DTO\Repository\SystemEventRepositoryDTO;
use App\Domain\SystemEvent\Service\SystemEventLinkGeneratorService;
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Domain\User\Service\UserService;
use App\Infrastructure\Service\PaginatorService;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};
use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Domain\SystemEventRecipient\Bus\Query\SystemEventsQueryByStatus\{GetSystemEventsQueryByStatusQuery, GetSystemEventsQueryByStatusQueryResult};

readonly class SystemEventTypeEventsHandle
{
    public function __construct(
        private UserService $userService,
        private PaginatorService $paginatorService,
        private QueryBusInterface $queryBus,
        private SystemEventLinkGeneratorService $systemEventLinkGeneratorService
    ) {}

    public function __invoke(Request $request, string $type): JsonResponse
    {
        $viewed = match ($type) {
            SystemEventStatusConstant::READ => true,
            SystemEventStatusConstant::UNREAD => false,
            SystemEventStatusConstant::ALL => null,
            default => throw new InvalidArgumentException(sprintf('Status %s not exist', $type))
        };

        $systemEventRepositoryData = new SystemEventRepositoryDTO(
            recipient: $this->userService->getUser(),
            viewed: $viewed
        );

        $getSystemEventsQueryByStatusQuery = GetSystemEventsQueryByStatusQuery::create($systemEventRepositoryData);
        /** @var GetSystemEventsQueryByStatusQueryResult $getSystemEventsQueryByStatusQueryResult */
        $getSystemEventsQueryByStatusQueryResult = $this->queryBus->handle($getSystemEventsQueryByStatusQuery);
        $worksQuery = $getSystemEventsQueryByStatusQueryResult->query;
        $pagination = $this->paginatorService->createPaginationRequest($request, $worksQuery);

        $systemEvents = [];
        /** @var SystemEventRecipient $systemEventRecipient */
        foreach ($pagination as $systemEventRecipient) {
            $systemEvents[] = [
                'id' => $systemEventRecipient->getId(),
                'title' => strip_tags($this->systemEventLinkGeneratorService->generateText($systemEventRecipient)),
                'owner' => $systemEventRecipient->getSystemEvent()->getOwner()->getFullNameDegree(),
                'viewed' => $systemEventRecipient->isViewed(),
                'createdAt' => $systemEventRecipient->getSystemEvent()->getCreatedAt()->format(DateFormatConstant::DATABASE->value),
            ];
        }

        return new JsonResponse([
            'count' => $pagination->count(),
            'totalCount' => $pagination->getTotalItemCount(),
            'success' => true,
            'result' => $systemEvents
        ]);
    }
}
