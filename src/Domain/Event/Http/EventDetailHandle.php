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

use App\Application\Constant\FlashTypeConstant;
use App\Application\Form\Factory\FormDeleteFactory;
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Domain\Comment\Bus\Command\CreateComment\CreateCommentCommand;
use App\Domain\Comment\Entity\Comment;
use App\Application\Service\{
    RequestService,
    SeoPageService,
    TwigRenderService
};
use App\Domain\Comment\Facade\CommentFacade;
use App\Domain\Comment\Form\CommentForm;
use App\Domain\Comment\Model\CommentModel;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\EventDispatcher\EventEventDispatcher;
use App\Domain\EventAddress\Facade\EventAddressFacade;
use App\Domain\User\Service\UserService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class EventDetailHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private CommentFacade $commentFacade,
        private SeoPageService $seoPageService,
        private FormFactoryInterface $formFactory,
        private EventAddressFacade $eventAddressFacade,
        private FormDeleteFactory $formDeleteFactory,
        private EventEventDispatcher $eventEventDispatcher,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request, Event $event): Response
    {
        $user = $this->userService->getUser();
        $eventCommentExist = $this->commentFacade
            ->getCommentByOwnerEvent($user, $event);

        $eventCommentModel = new CommentModel($user, $event);

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
            /** @var Comment $eventComment */
            $eventComment = $this->commandBus->dispatchResult($command);

            $this->eventEventDispatcher->onEventComment(
                $eventComment,
                $eventCommentExist !== null
            );

            $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.save.success');
        }

        $eventAddressSkype = $this->eventAddressFacade
            ->getSkypeByOwner($event->getOwner());

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
