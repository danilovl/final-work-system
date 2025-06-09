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

namespace App\Domain\EventSchedule\Factory;

use App\Application\Constant\DateFormatConstant;
use App\Application\Factory\Model\BaseModelFactory;
use App\Application\Helper\DateHelper;
use App\Domain\Event\Entity\Event;
use App\Domain\EventSchedule\Entity\EventSchedule;
use App\Domain\EventSchedule\Model\EventScheduleModel;
use App\Domain\User\Entity\User;
use DateTime;

class EventScheduleFactory extends BaseModelFactory
{
    public function cloneEventSchedule(
        User $user,
        EventSchedule $eventSchedule,
        DateTime $startDate
    ): void {
        $templates = $eventSchedule->getTemplates();

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
                $startDate->format(DateFormatConstant::DATE->value),
                $template->getStart()->format(DateFormatConstant::TIME->value)
            );

            $eventStartDate = DateHelper::plusDayDate($dateFormat, $template->getDay());
            $event->setStart(new DateTime($eventStartDate));

            $eventEndDate = DateHelper::plusDayDate($dateFormat, $template->getDay());
            $event->setEnd(new DateTime($eventEndDate));

            $this->entityManagerService->persistAndFlush($event);
        }
    }

    public function flushFromModel(
        EventScheduleModel $eventAddressModel,
        ?EventSchedule $eventSchedule = null
    ): EventSchedule {
        $eventSchedule ??= new EventSchedule;
        $eventSchedule = $this->fromModel($eventSchedule, $eventAddressModel);

        $this->entityManagerService->persistAndFlush($eventSchedule);

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
