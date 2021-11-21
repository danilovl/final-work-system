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

namespace App\Model\EventCalendar\Http;

use App\Model\EventCalendar\Form\EventWorkReservationForm;
use App\Model\WorkStatus\Entity\WorkStatus;
use App\Constant\{
    WorkStatusConstant,
    WorkUserTypeConstant
};
use App\Model\EventWorkReservation\EventWorkReservationModel;
use App\Model\User\Service\UserWorkService;
use App\Service\{
    UserService,
    TwigRenderService,
    EntityManagerService
};
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;

class EventCalendarReservationHandle
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private UserService $userService,
        private UserWorkService $userWorkService,
        private TwigRenderService $twigRenderService,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function handle(): Response
    {
        $userWorks = $this->userWorkService->getWorkBy(
            $this->userService->getUser(),
            WorkUserTypeConstant::AUTHOR,
            null,
            $this->entityManagerService->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE)
        );

        $form = $this->formFactory->create(EventWorkReservationForm::class, new EventWorkReservationModel, [
            'works' => $userWorks
        ]);

        return $this->twigRenderService->render('event/calendar_reservation.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
