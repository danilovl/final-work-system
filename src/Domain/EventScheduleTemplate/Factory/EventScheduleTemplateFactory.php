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

namespace App\Domain\EventScheduleTemplate\Factory;

use App\Application\Factory\Model\BaseModelFactory;
use App\Domain\EventScheduleTemplate\Entity\EventScheduleTemplate;
use App\Domain\EventScheduleTemplate\Model\EventScheduleTemplateModel;

class EventScheduleTemplateFactory extends BaseModelFactory
{
    public function createFromModel(
        EventScheduleTemplateModel $eventScheduleTemplateModel,
        ?EventScheduleTemplate $eventScheduleTemplate = null
    ): EventScheduleTemplate {
        $eventScheduleTemplate ??= new EventScheduleTemplate;
        $eventScheduleTemplate = $this->fromModel($eventScheduleTemplate, $eventScheduleTemplateModel);

        $this->entityManagerService->persistAndFlush($eventScheduleTemplate);

        return $eventScheduleTemplate;
    }

    public function fromModel(
        EventScheduleTemplate $eventScheduleTemplate,
        EventScheduleTemplateModel $eventScheduleTemplateModel
    ): EventScheduleTemplate {
        $eventScheduleTemplate->setType($eventScheduleTemplateModel->type);
        $eventScheduleTemplate->setDay($eventScheduleTemplateModel->day);
        $eventScheduleTemplate->setName($eventScheduleTemplateModel->name);
        $eventScheduleTemplate->setAddress($eventScheduleTemplateModel->address);
        $eventScheduleTemplate->setStart($eventScheduleTemplateModel->start);
        $eventScheduleTemplate->setEnd($eventScheduleTemplateModel->end);

        return $eventScheduleTemplate;
    }
}
