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

use App\Application\Helper\CloneHelper;
use App\Application\Service\{
    SeoPageService,
    PaginatorService,
    TwigRenderService
};
use App\Domain\Event\DataTransferObject\EventRepositoryData;
use App\Domain\Event\Facade\EventFacade;
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
        private EventFacade $eventFacade,
        private PaginatorService $paginatorService,
        private SeoPageService $seoPageService,
    ) {}

    public function handle(Request $request): Response
    {
        $user = $this->userService->getUser();
        $eventRepositoryData = new EventRepositoryData;
        $eventRepositoryData->user = $user;

        $eventsQuery = $this->eventFacade->getEventsByOwnerQuery($eventRepositoryData);

        $pagination = $this->paginatorService->createPaginationRequest($request, $eventsQuery);
        /** @var object[] $items */
        $items = iterator_to_array($pagination->getItems());
        $pagination->setItems(CloneHelper::simpleCloneObjects($items));

        $this->seoPageService->setTitle('app.page.event_list');

        return $this->twigRenderService->renderToResponse('domain/event/list.html.twig', [
            'events' => $pagination
        ]);
    }
}
