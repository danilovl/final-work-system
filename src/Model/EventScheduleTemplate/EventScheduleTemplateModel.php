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

namespace App\Model\EventScheduleTemplate;

use App\Model\EventAddress\Entity\EventAddress;
use App\Model\EventScheduleTemplate\Entity\EventScheduleTemplate;
use App\Model\EventType\Entity\EventType;
use DateTime;

class EventScheduleTemplateModel
{
    public ?EventType $type = null;
    public ?int $day = null;
    public ?string $name = null;
    public ?EventAddress $address = null;
    public ?DateTime $start = null;
    public ?DateTime $end = null;

    public static function fromEventScheduleTemplate(EventScheduleTemplate $eventScheduleTemplate): self
    {
        $model = new self;
        $model->type = $eventScheduleTemplate->getType();
        $model->day = $eventScheduleTemplate->getDay();
        $model->name = $eventScheduleTemplate->getName();
        $model->address = $eventScheduleTemplate->getAddress();
        $model->start = $eventScheduleTemplate->getStart();
        $model->end = $eventScheduleTemplate->getEnd();

        return $model;
    }
}
