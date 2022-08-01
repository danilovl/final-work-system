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

use App\Domain\Work\Entity\Work;
use App\Application\Constant\{
    WorkStatusConstant,
    WorkUserTypeConstant
};
use App\Application\Helper\SortFunctionHelper;
use App\Application\Service\{
    UserService,
    TwigRenderService,
    EntityManagerService
};
use App\Domain\Event\EventModel;
use App\Domain\Event\Form\EventForm;
use App\Domain\EventParticipant\Entity\EventParticipant;
use App\Domain\User\Service\UserWorkService;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

class EventCalendarManageHandle
{
    public function __construct(
        private readonly EntityManagerService $entityManagerService,
        private readonly UserService $userService,
        private readonly UserWorkService $userWorkService,
        private readonly TwigRenderService $twigRenderService,
        private readonly FormFactoryInterface $formFactory
    ) {}

    public function handle(): Response
    {
        $user = $this->userService->getUser();
        /** @var WorkStatus $workStatus */
        $workStatus = $this->entityManagerService->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE);

        $userWorks = $this->userWorkService->getWorkBy(
            $user,
            WorkUserTypeConstant::SUPERVISOR,
            null,
            $workStatus
        );
        $eventParticipantArray = [];

        /** @var Work $work */
        foreach ($userWorks as $work) {
            $eventParticipant = new EventParticipant;
            $eventParticipant->setUser($work->getAuthor());
            $eventParticipant->setWork($work);
            $eventParticipantArray[] = $eventParticipant;
        }

        SortFunctionHelper::eventParticipantSort($eventParticipantArray);

        $eventModel = new EventModel;
        $eventModel->owner = $user;

        $form = $this->formFactory->create(EventForm::class, $eventModel, [
            'addresses' => $user->getEventAddressOwner(),
            'participants' => $eventParticipantArray
        ]);

        return $this->twigRenderService->render('event/calendar_manage.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
