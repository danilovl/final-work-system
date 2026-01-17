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

use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\EventSchedule\Bus\Query\EventScheduleList\{
    GetEventScheduleListQuery,
    GetEventScheduleListQueryResult
};
use App\Infrastructure\Service\TwigRenderService;
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
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $this->userService->getUser();

        $query = GetEventScheduleListQuery::create($request, $user);
        /** @var GetEventScheduleListQueryResult $result */
        $result = $this->queryBus->handle($query);

        return $this->twigRenderService->renderToResponse('domain/event_schedule/list.html.twig', [
            'eventSchedules' => $result->eventSchedules
        ]);
    }
}
