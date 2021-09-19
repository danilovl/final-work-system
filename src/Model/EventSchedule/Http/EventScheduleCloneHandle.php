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

namespace App\Model\EventSchedule\Http;

use App\Entity\EventSchedule;
use App\Form\EventScheduleCloneForm;
use App\Model\EventSchedule\EventScheduleCloneModel;
use App\Model\EventSchedule\Factory\EventScheduleFactory;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use App\Constant\FlashTypeConstant;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\{
    UserService,
    RequestService,
    SeoPageService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\Routing\RouterInterface;

class EventScheduleCloneHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private HashidsServiceInterface $hashidsService,
        private EventScheduleFactory $eventScheduleFactory,
        private FormFactoryInterface $formFactory,
        private SeoPageService $seoPageService,
        private RouterInterface $router
    ) {
    }

    public function handle(Request $request, EventSchedule $eventSchedule): Response
    {
        $user = $this->userService->getUser();

        $eventScheduleCloneModel = new EventScheduleCloneModel;
        $form = $this->formFactory
            ->create(EventScheduleCloneForm::class, $eventScheduleCloneModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->eventScheduleFactory->cloneEventSchedule(
                    $user,
                    $eventSchedule,
                    $eventScheduleCloneModel->start
                );

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->requestService->redirectToRoute('event_schedule_list');
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->formFactory->create(EventScheduleCloneForm::class, null, [
                'action' => $this->router->generate('event_schedule_clone_ajax', [
                    'id' => $this->hashidsService->encode($eventSchedule->getId())
                ]),
                'method' => Request::METHOD_POST
            ]);
        }

        $this->seoPageService->setTitle($eventSchedule->getName());

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'event_schedule/clone.html.twig');

        return $this->twigRenderService->render($template, [
            'eventSchedule' => $eventSchedule,
            'form' => $form->createView()
        ]);
    }
}