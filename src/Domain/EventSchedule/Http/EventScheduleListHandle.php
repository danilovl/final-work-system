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

namespace App\Domain\EventSchedule\Http;

use App\Application\Service\{
    PaginatorService,
    TwigRenderService
};
use App\Domain\EventSchedule\Facade\EventScheduleFacade;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class EventScheduleListHandle
{
    public function __construct(
        private TwigRenderService $twigRenderService,
        private UserService $userService,
        private EventScheduleFacade $eventScheduleFacade,
        private PaginatorService $paginatorService
    ) {}

    public function handle(Request $request): Response
    {
        $user = $this->userService->getUser();

        $eventSchedulesQuery = $this->eventScheduleFacade
            ->queryEventSchedulesByOwner($user);

        return $this->twigRenderService->renderToResponse('domain/event_schedule/list.html.twig', [
            'eventSchedules' => $this->paginatorService->createPaginationRequest($request, $eventSchedulesQuery)
        ]);
    }
}
