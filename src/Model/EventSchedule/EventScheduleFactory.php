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

namespace App\Model\EventSchedule;

use App\Constant\DateFormatConstant;
use DateTime;
use App\Model\BaseModelFactory;
use App\Entity\{
    Event,
    EventSchedule,
    EventScheduleTemplate
};
use App\Helper\DateHelper;
use App\Entity\User;

class EventScheduleFactory extends BaseModelFactory
{
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

            $dateFormat = sprintf('%s %s',
                $startDate->format(DateFormatConstant::DATE),
                $template->getStart()->format(DateFormatConstant::TIME)
            );

            $startDate = DateHelper::plusDayDate($dateFormat, $template->getDay());
            $event->setStart(new DateTime($startDate));

            $endDate = DateHelper::plusDayDate($dateFormat, $template->getDay());
            $event->setEnd(new DateTime($endDate));

            $this->em->persistAndFlush($event);
        }
    }

    public function flushFromModel(
        EventScheduleModel $eventAddressModel,
        EventSchedule $eventSchedule = null
    ): EventSchedule {
        $eventSchedule = $eventSchedule ?? new EventSchedule;
        $eventSchedule = $this->fromModel($eventSchedule, $eventAddressModel);

        $this->em->persistAndFlush($eventSchedule);

        return $eventSchedule;
    }

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
