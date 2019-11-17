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

namespace FinalWork\FinalWorkBundle\Model\EventSchedule;

use DateTime;
use FinalWork\FinalWorkBundle\Model\BaseModelFactory;
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};
use Exception;
use FinalWork\FinalWorkBundle\Entity\{
    Event,
    EventSchedule,
    EventScheduleTemplate
};
use FinalWork\FinalWorkBundle\Helper\DateHelper;
use FinalWork\SonataUserBundle\Entity\User;

class EventScheduleFactory extends BaseModelFactory
{
    /**
     * @param User $user
     * @param EventSchedule $eventSchedule
     * @param DateTime $startDate
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    public function cloneEventSchedule(
        User $user,
        EventSchedule $eventSchedule,
        DateTime $startDate
    ): void {
        $templates = $eventSchedule->getTemplates();

        /** @var EventScheduleTemplate $template */
        foreach ($templates as $template) {
            $event = new Event;
            $event->setType($template->getType());
            $event->setOwner($user);

            $name = $template->getName();
            if ($name !== null) {
                $event->setName($name);
            }

            $address = $template->getAddress();
            if ($address !== null) {
                $event->setAddress($address);
            }

            $startDate = DateHelper::plusDayDate(
                $startDate->format('Y-m-d') . ' ' . $template->getStart()->format('H:i:s'),
                $template->getDay()
            );
            $start = new DateTime($startDate);
            $event->setStart($start);

            $endDate = DateHelper::plusDayDate(
                $startDate->format('Y-m-d') . ' ' . $template->getEnd()->format('H:i:s'),
                $template->getDay()
            );
            $end = new DateTime($endDate);
            $event->setEnd($end);

            $this->em->persist($event);
        }

        $this->em->flush();
    }

    /**
     * @param EventScheduleModel $eventAddressModel
     * @param EventSchedule|null $eventSchedule
     * @return EventSchedule
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function flushFromModel(
        EventScheduleModel $eventAddressModel,
        EventSchedule $eventSchedule = null
    ): EventSchedule {
        $eventSchedule = $eventSchedule ?? new EventSchedule;
        $eventSchedule = $this->fromModel($eventSchedule, $eventAddressModel);

        $this->em->persist($eventSchedule);
        $this->em->flush();

        return $eventSchedule;
    }

    /**
     * @param EventSchedule $eventSchedule
     * @param EventScheduleModel $eventScheduleModel
     * @return EventSchedule
     */
    public function fromModel(
        EventSchedule $eventSchedule,
        EventScheduleModel $eventScheduleModel
    ): EventSchedule {
        $eventSchedule->setName($eventScheduleModel->name);
        $eventSchedule->setDescription($eventScheduleModel->description);
        $eventSchedule->setOwner($eventScheduleModel->owner);
        $eventSchedule->setTemplates($eventScheduleModel->templates);

        return $eventSchedule;
    }
}
