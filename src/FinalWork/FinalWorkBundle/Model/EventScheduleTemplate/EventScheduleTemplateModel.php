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

namespace FinalWork\FinalWorkBundle\Model\EventScheduleTemplate;

use FinalWork\FinalWorkBundle\Entity\EventScheduleTemplate;
use Symfony\Component\Validator\Constraints as Assert;

class EventScheduleTemplateModel
{
    /**
     * @Assert\NotBlank()
     */
    public $type;

    /**
     * @Assert\NotBlank()
     */
    public $day;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @Assert\NotBlank()
     */
    public $address;

    /**
     * @Assert\Time()
     * @Assert\NotBlank()
     */
    public $start;

    /**
     * @Assert\Time()
     * @Assert\NotBlank()
     */
    public $end;

    /**
     * @param EventScheduleTemplate $eventScheduleTemplate
     * @return EventScheduleTemplateModel
     */
    public static function fromEventScheduleTemplate(EventScheduleTemplate $eventScheduleTemplate): self
    {
        $model = new self();
        $model->type = $eventScheduleTemplate->getType();
        $model->day = $eventScheduleTemplate->getDay();
        $model->name = $eventScheduleTemplate->getName();
        $model->address = $eventScheduleTemplate->getAddress();
        $model->start = $eventScheduleTemplate->getStart();
        $model->end = $eventScheduleTemplate->getEnd();

        return $model;
    }
}
