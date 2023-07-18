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

namespace App\Domain\EventCalendar\Http;

use App\Application\Service\{
    EntityManagerService,
    TwigRenderService};
use App\Domain\EventCalendar\Form\EventWorkReservationForm;
use App\Domain\EventWorkReservation\Model\EventWorkReservationModel;
use App\Domain\User\Service\{
    UserService,
    UserWorkService};
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

readonly class EventCalendarReservationHandle
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private UserService $userService,
        private UserWorkService $userWorkService,
        private TwigRenderService $twigRenderService,
        private FormFactoryInterface $formFactory
    ) {}

    public function handle(): Response
    {
        /** @var WorkStatus $workStatus */
        $workStatus = $this->entityManagerService->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE->value);

        $userWorks = $this->userWorkService->getWorkBy(
            $this->userService->getUser(),
            WorkUserTypeConstant::AUTHOR->value,
            null,
            $workStatus
        );

        $form = $this->formFactory->create(EventWorkReservationForm::class, new EventWorkReservationModel, [
            'works' => $userWorks
        ]);

        return $this->twigRenderService->render('event/calendar_reservation.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
