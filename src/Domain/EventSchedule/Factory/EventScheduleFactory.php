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

use App\Infrastructure\Constant\DateFormatConstant;
use App\Application\Factory\Model\BaseModelFactory;
use App\Infrastructure\Helper\DateHelper;
use App\Domain\Event\Entity\Event;
use App\Domain\EventSchedule\Entity\EventSchedule;
use App\Domain\EventSchedule\Model\EventScheduleModel;
use App\Domain\EventScheduleTemplate\Entity\EventScheduleTemplate;
use App\Domain\EventScheduleTemplate\Factory\EventScheduleTemplateFactory;
use App\Domain\User\Entity\User;
use App\Infrastructure\Service\EntityManagerService;
use DateTimeImmutable;

class EventScheduleFactory extends BaseModelFactory
{
    public function __construct(
        EntityManagerService $entityManagerService,
        private readonly EventScheduleTemplateFactory $eventScheduleTemplateFactory
    ) {
        parent::__construct($entityManagerService);
    }

    public function cloneEventSchedule(
        User $user,
        EventSchedule $eventSchedule,
        DateTimeImmutable $startDate
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
            $event->setStart(new DateTimeImmutable($eventStartDate));

            $eventEndDate = DateHelper::plusDayDate($dateFormat, $template->getDay());
            $event->setEnd(new DateTimeImmutable($eventEndDate));

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
        $this->syncTemplates($eventSchedule, $eventScheduleModel);

        return $eventSchedule;
    }

    private function syncTemplates(EventSchedule $eventSchedule, EventScheduleModel $eventScheduleModel): void
    {
        /** @var array<int, EventScheduleTemplate> $existingTemplates */
        $existingTemplates = [];
        foreach ($eventSchedule->getTemplates() as $template) {
            $existingTemplates[$template->getId()] = $template;
        }

        $keepTemplateIds = [];
        foreach ($eventScheduleModel->templates as $templateModel) {
            $template = $templateModel->id !== null ? ($existingTemplates[$templateModel->id] ?? null) : null;

            if ($template === null) {
                $template = new EventScheduleTemplate;
                $eventSchedule->addTemplate($template);
            } else {
                $keepTemplateIds[$templateModel->id] = true;
            }

            $this->eventScheduleTemplateFactory->fromModel($template, $templateModel);
        }

        foreach ($existingTemplates as $id => $template) {
            if (isset($keepTemplateIds[$id])) {
                continue;
            }

            $eventSchedule->removeTemplate($template);
            $this->entityManagerService->remove($template);
        }
    }
}
