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

use App\Application\Constant\DateFormatConstant;
use App\Application\Exception\InvalidArgumentException;
use App\Application\Service\PaginatorService;
use App\Domain\SystemEvent\Constant\SystemEventStatusConstant;
use App\Domain\SystemEvent\DataTransferObject\SystemEventRepositoryData;
use App\Domain\SystemEvent\Facade\SystemEventRecipientFacade;
use App\Domain\SystemEvent\Service\SystemEventLinkGeneratorService;
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

readonly class SystemEventTypeEventsHandle
{
    public function __construct(
        private UserService $userService,
        private PaginatorService $paginatorService,
        private SystemEventRecipientFacade $systemEventRecipientFacade,
        private SystemEventLinkGeneratorService $systemEventLinkGeneratorService
    ) {}

    public function handle(Request $request, string $type): JsonResponse
    {
        $viewed = match ($type) {
            SystemEventStatusConstant::READ => true,
            SystemEventStatusConstant::UNREAD => false,
            SystemEventStatusConstant::ALL => null,
            default => throw new InvalidArgumentException(sprintf('Status %s not exist', $type))
        };

        $systemEventRepositoryData = new SystemEventRepositoryData;
        $systemEventRepositoryData->viewed = $viewed;
        $systemEventRepositoryData->recipient = $this->userService->getUser();

        $worksQuery = $this->systemEventRecipientFacade->querySystemEventsByStatus($systemEventRepositoryData);
        $pagination = $this->paginatorService->createPaginationRequest($request, $worksQuery);

        $systemEvents = [];
        /** @var SystemEventRecipient $systemEEventRecipient */
        foreach ($pagination as $systemEEventRecipient) {
            $systemEvents[] = [
                'id' => $systemEEventRecipient->getSystemEvent()->getId(),
                'title' => $this->systemEventLinkGeneratorService->generateText($systemEEventRecipient),
                'owner' => $systemEEventRecipient->getSystemEvent()->getOwner()->getFullNameDegree(),
                'createdAt' => $systemEEventRecipient->getSystemEvent()->getCreatedAt()->format(DateFormatConstant::DATABASE->value),
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
