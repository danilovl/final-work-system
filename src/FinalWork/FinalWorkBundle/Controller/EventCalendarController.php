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

namespace FinalWork\FinalWorkBundle\Controller;

use Doctrine\ORM\ORMException;
use FinalWork\FinalWorkBundle\Model\Event\EventModel;
use FinalWork\FinalWorkBundle\Model\EventWorkReservation\EventWorkReservationModel;
use FinalWork\FinalWorkBundle\Constant\{
    WorkStatusConstant,
    WorkUserTypeConstant
};
use FinalWork\FinalWorkBundle\Entity\{
    Work,
    EventParticipant,
    WorkStatus
};
use FinalWork\FinalWorkBundle\Form\{
    EventForm,
    EventWorkReservationForm
};
use FinalWork\FinalWorkBundle\Helper\SortFunctionHelper;
use LogicException;
use Symfony\Component\HttpFoundation\Response;

class EventCalendarController extends BaseController
{
    /**
     * @return Response
     *
     * @throws ORMException
     */
    public function reservationAction(): Response
    {
        $userWorks = $this->getUser()->getWorkBy(
            WorkUserTypeConstant::AUTHOR,
            null,
            $this->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE)
        );

        $form = $this->createForm(EventWorkReservationForm::class, new EventWorkReservationModel, [
            'works' => $userWorks
        ]);

        $this->get('final_work.seo_page')->setTitle('finalwork.page.appointment_calendar');

        return $this->render('@FinalWork/event/calendar_reservation.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @return Response
     *
     * @throws ORMException
     */
    public function manageAction(): Response
    {
        $user = $this->getUser();

        $userWorks = $user->getWorkBy(
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

        $this->get('final_work.seo_page')->setTitle('finalwork.page.appointment_calendar');

        return $this->render('@FinalWork/event/calendar_manage.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
