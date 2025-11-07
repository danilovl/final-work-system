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

namespace App\Domain\Event\Http;

use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\Event\Bus\Query\EventList\{
    GetEventListQuery,
    GetEventListQueryResult
};
use App\Application\Service\{
    SeoPageService,
    TwigRenderService
};
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class EventListHandle
{
    public function __construct(
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private SeoPageService $seoPageService,
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $this->userService->getUser();

        $query = GetEventListQuery::create($request, $user);
        /** @var GetEventListQueryResult $result */
        $result = $this->queryBus->handle($query);

        $this->seoPageService->setTitle('app.page.event_list');

        return $this->twigRenderService->renderToResponse('domain/event/list.html.twig', [
            'events' => $result->events
        ]);
    }
}
