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

use App\Infrastructure\Service\{
    EntityManagerService,
    TwigRenderService
};
use App\Domain\EventCalendar\Form\EventWorkReservationForm;
use App\Domain\EventWorkReservation\Model\EventWorkReservationModel;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\Work\Bus\Query\UserWorks\UserWorksQueryTrait;

readonly class EventCalendarReservationHandle
{
    use UserWorksQueryTrait;

    public function __construct(
        private EntityManagerService $entityManagerService,
        private UserService $userService,
        private QueryBusInterface $queryBus,
        private TwigRenderService $twigRenderService,
        private FormFactoryInterface $formFactory
    ) {}

    public function __invoke(): Response
    {
        /** @var WorkStatus $workStatus */
        $workStatus = $this->entityManagerService->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE->value);

        $userWorks = $this->getUserWorks(
            $this->userService->getUser(),
            WorkUserTypeConstant::AUTHOR->value,
            null,
            $workStatus
        );

        $form = $this->formFactory->create(EventWorkReservationForm::class, new EventWorkReservationModel, [
            'works' => $userWorks
        ]);

        return $this->twigRenderService->renderToResponse('domain/event/calendar_reservation.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
