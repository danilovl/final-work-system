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

namespace App\Domain\Event\Http\Api;

use Danilovl\ObjectDtoMapper\Service\ObjectToDtoMapperInterface;
use App\Domain\Event\DTO\Api\EventDTO;
use App\Domain\Event\Entity\Event;
use App\Domain\Work\Service\WorkDetailTabService;
use App\Application\Constant\TabTypeConstant;
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

readonly class EventListHandle
{
    public function __construct(
        private WorkDetailTabService $workDetailTabService,
        private ObjectToDtoMapperInterface $objectToDtoMapper
    ) {}

    public function __invoke(Request $request, Work $work): JsonResponse
    {
        $pagination = $this->workDetailTabService->getTabPagination(
            request: $request,
            tab: TabTypeConstant::TAB_EVENT->value,
            work: $work,
            isApi: true
        );

        $events = [];
        /** @var Event $event */
        foreach ($pagination as $event) {
            $events[] = $this->objectToDtoMapper->map($event, EventDTO::class);
        }

        return new JsonResponse([
            'count' => $pagination->count(),
            'totalCount' => $pagination->getTotalItemCount(),
            'success' => true,
            'result' => $events
        ]);
    }
}
