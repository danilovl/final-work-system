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

use App\Form\EventScheduleForm;
use App\Model\EventSchedule\EventScheduleModel;
use App\Model\EventSchedule\Factory\EventScheduleFactory;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use App\Entity\EventSchedule;
use App\Constant\FlashTypeConstant;
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

class EventScheduleEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private HashidsServiceInterface $hashidsService,
        private EventScheduleFactory $eventScheduleFactory,
        private FormFactoryInterface $formFactory,
        private SeoPageService $seoPageService
    ) {
    }

    public function handle(Request $request, EventSchedule $eventSchedule): Response
    {
        $user = $this->userService->getUser();

        $eventScheduleModel = EventScheduleModel::fromEventSchedule($eventSchedule);
        $form = $this->formFactory
            ->create(EventScheduleForm::class, $eventScheduleModel, [
                'addresses' => $user->getEventAddressOwner()
            ])
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->eventScheduleFactory
                    ->flushFromModel($eventScheduleModel, $eventSchedule);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.save.success');

                return $this->requestService->redirectToRoute('event_schedule_detail', [
                    'id' => $this->hashidsService->encode($eventSchedule->getId())
                ]);
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.save.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.save.error');
        }

        $this->seoPageService->setTitle($eventSchedule->getName());

        return $this->twigRenderService->render('event_schedule/edit.html.twig', [
            'eventSchedule' => $eventSchedule,
            'form' => $form->createView()
        ]);
    }
}
