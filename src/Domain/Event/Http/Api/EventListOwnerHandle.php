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
use App\Domain\Event\DTO\Api\Output\EventListOwnerOutput;
use App\Domain\Event\DTO\Repository\EventRepositoryDTO;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Facade\EventFacade;
use App\Domain\User\Service\UserService;
use App\Infrastructure\Service\PaginatorService;
use Symfony\Component\HttpFoundation\Request;

readonly class EventListOwnerHandle
{
    public function __construct(
        private UserService $userService,
        private EventFacade $eventFacade,
        private PaginatorService $paginatorService,
        private ObjectToDtoMapperInterface $objectToDtoMapper,
    ) {}

    public function __invoke(Request $request): EventListOwnerOutput
    {
        $user = $this->userService->getUser();

        $eventData = new EventRepositoryDTO(user: $user);

        $eventsQuery = $this->eventFacade
            ->queryEventsByOwner($eventData);

        $pagination = $this->paginatorService->createPaginationRequest(
            request: $request,
            target: $eventsQuery,
            limit: 1
        );

        $result = [];

        /** @var Event $event */
        foreach ($pagination->getItems() as $event) {
            $result[] = $this->objectToDtoMapper->map($event, EventDTO::class);
        }

        return new EventListOwnerOutput(
            numItemsPerPage: $pagination->getItemNumberPerPage(),
            totalCount: $pagination->getTotalItemCount(),
            currentItemCount: $pagination->count(),
            result: $result
        );
    }
}
