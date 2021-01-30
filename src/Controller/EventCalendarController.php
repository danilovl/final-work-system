<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Controller;

use App\Model\Event\EventModel;
use App\Model\EventWorkReservation\EventWorkReservationModel;
use App\Constant\{
    WorkStatusConstant,
    WorkUserTypeConstant
};
use App\Entity\{
    Work,
    EventParticipant,
    WorkStatus
};
use App\Form\{
    EventForm,
    EventWorkReservationForm
};
use App\Helper\SortFunctionHelper;
use Symfony\Component\HttpFoundation\Response;

class EventCalendarController extends BaseController
{
    public function reservation(): Response
    {
        $userWorks = $this->get('app.user_work')->getWorkBy(
            $this->getUser(),
            WorkUserTypeConstant::AUTHOR,
            null,
            $this->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE)
        );

        $form = $this->createForm(EventWorkReservationForm::class, new EventWorkReservationModel, [
            'works' => $userWorks
        ]);

        return $this->render('event/calendar_reservation.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function manage(): Response
    {
        $user = $this->getUser();

        $userWorks = $this->get('app.user_work')->getWorkBy(
            $user,
            WorkUserTypeConstant::SUPERVISOR,
            null,
            $this->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE)
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

        $form = $this->createForm(EventForm::class, $eventModel, [
            'addresses' => $user->getEventAddressOwner(),
            'participants' => $eventParticipantArray
        ]);

        return $this->render('event/calendar_manage.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
