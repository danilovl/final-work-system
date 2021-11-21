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

namespace App\Model\Event\Http;

use App\Constant\FlashTypeConstant;
use App\Model\Event\Entity\Event;
use App\Model\Comment\Form\CommentForm;
use App\Form\Factory\FormDeleteFactory;
use App\Model\Comment\CommentModel;
use App\Model\Comment\Facade\CommentFacade;
use App\Model\Comment\Factory\CommentFactory;
use App\Model\Event\EventDispatcher\EventEventDispatcherService;
use App\Model\EventAddress\Facade\EventAddressFacade;
use App\Service\{
    UserService,
    RequestService,
    SeoPageService,
    TwigRenderService
};
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class EventDetailHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private CommentFacade $commentFacade,
        private SeoPageService $seoPageService,
        private FormFactoryInterface $formFactory,
        private CommentFactory $commentFactory,
        private EventAddressFacade $eventAddressFacade,
        private FormDeleteFactory $formDeleteFactory,
        private EventEventDispatcherService $eventEventDispatcherService
    ) {
    }

    public function handle(Request $request, Event $event): Response
    {
        $user = $this->userService->getUser();
        $eventCommentExist = $this->commentFacade
            ->getCommentByOwnerEvent($user, $event);

        $eventCommentModel = new CommentModel;
        $eventCommentModel->owner = $user;
        $eventCommentModel->event = $event;

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
            $eventComment = $this->commentFactory
                ->createFromModel($eventCommentModel, $eventCommentExist);

            $this->eventEventDispatcherService
                ->onEventComment($eventComment, $eventCommentExist !== null);

            $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.save.success');
        }

        $eventAddressSkype = $this->eventAddressFacade
            ->getSkypeByOwner($event->getOwner());

        $this->seoPageService->setTitle($event->toString());

        $deleteForm = $this->formDeleteFactory
            ->createDeleteForm($event, 'event_delete')
            ->createView();

        return $this->twigRenderService->render('event/detail.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'deleteForm' => $deleteForm,
            'switchToSkype' => $eventAddressSkype !== null
        ]);
    }
}
