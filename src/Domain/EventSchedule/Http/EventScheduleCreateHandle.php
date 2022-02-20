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

use App\Application\Constant\FlashTypeConstant;
use App\Application\Service\{
    UserService,
    RequestService,
    TwigRenderService
};
use App\Domain\EventSchedule\EventScheduleModel;
use App\Domain\EventSchedule\Factory\EventScheduleFactory;
use App\Domain\EventSchedule\Form\EventScheduleForm;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class EventScheduleCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private EventScheduleFactory $eventScheduleFactory,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function handle(Request $request): Response
    {
        $user = $this->userService->getUser();

        $eventScheduleModel = new EventScheduleModel;
        $eventScheduleModel->owner = $user;

        $form = $this->formFactory
            ->create(EventScheduleForm::class, $eventScheduleModel, [
                'addresses' => $user->getEventAddressOwner()
            ])
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->eventScheduleFactory->flushFromModel($eventScheduleModel);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->requestService->redirectToRoute('event_schedule_list');
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        return $this->twigRenderService->render('event_schedule/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
}