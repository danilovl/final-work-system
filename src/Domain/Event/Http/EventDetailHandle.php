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

use App\Infrastructure\Interfaces\Bus\{CommandBusInterface, QueryBusInterface};
use App\Domain\Comment\Bus\Query\EventComment\{GetEventCommentQuery, GetEventCommentQueryResult};
use App\Domain\EventAddress\Bus\Query\SkypeEventAddress\{GetSkypeEventAddressQuery, GetSkypeEventAddressQueryResult};
use App\Infrastructure\Service\{
    SeoPageService,
    TwigRenderService
};
use App\Domain\Comment\Bus\Command\CreateComment\CreateCommentCommand;
use App\Domain\Comment\Form\CommentForm;
use App\Domain\Comment\Model\CommentModel;
use App\Domain\Event\Entity\Event;
use App\Domain\User\Service\UserService;
use App\Infrastructure\Web\Form\Factory\FormDeleteFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class EventDetailHandle
{
    public function __construct(
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private QueryBusInterface $queryBus,
        private SeoPageService $seoPageService,
        private FormFactoryInterface $formFactory,
        private FormDeleteFactory $formDeleteFactory,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request, Event $event): Response
    {
        $user = $this->userService->getUser();
        $getEventCommentQuery = GetEventCommentQuery::create(
            user: $user,
            event: $event
        );
        /** @var GetEventCommentQueryResult $getEventCommentQueryResult */
        $getEventCommentQueryResult = $this->queryBus->handle($getEventCommentQuery);
        $eventCommentExist = $getEventCommentQueryResult->comment;

        $eventCommentModel = new CommentModel(owner: $user, event: $event);

        if ($eventCommentExist !== null) {
            $eventCommentModel = CommentModel::fromComment($eventCommentExist);
        }

        $form = $this->formFactory
            ->create(CommentForm::class, $eventCommentModel, [
                'user' => $user,
                'event' => $event
            ])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = CreateCommentCommand::create($eventCommentModel, $eventCommentExist);
            $this->commandBus->dispatch($command);
        }

        $eventOwner = $event->getOwner();
        $getSkypeEventAddressQuery = GetSkypeEventAddressQuery::create($eventOwner);
        /** @var GetSkypeEventAddressQueryResult $getSkypeEventAddressQueryResult */
        $getSkypeEventAddressQueryResult = $this->queryBus->handle($getSkypeEventAddressQuery);
        $eventAddressSkype = $getSkypeEventAddressQueryResult->eventAddress;

        $this->seoPageService->setTitle($event->toString());

        $deleteForm = $this->formDeleteFactory
            ->createDeleteForm($event, 'event_delete')
            ->createView();

        return $this->twigRenderService->renderToResponse('domain/event/detail.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'deleteForm' => $deleteForm,
            'switchToSkype' => $eventAddressSkype !== null
        ]);
    }
}
