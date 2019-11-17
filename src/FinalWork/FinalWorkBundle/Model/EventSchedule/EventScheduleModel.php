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

use Doctrine\Common\Collections\ArrayCollection;
use FinalWork\FinalWorkBundle\Entity\EventSchedule;
use FinalWork\FinalWorkBundle\Model\Traits\SimpleInformationTrait;
use Symfony\Component\Validator\Constraints as Assert;

class EventScheduleModel
{
    use SimpleInformationTrait;

    /**
     * @Assert\NotBlank()
     */
    public $owner;

    /**
     * @Assert\NotBlank()
     */
    public $templates;

    /**
     * EventScheduleModel constructor.
     */
    public function __construct()
    {
        $this->templates = new ArrayCollection;
    }

    /**
     * @param EventSchedule $eventSchedule
     * @return EventScheduleModel
     */
    public static function fromEventSchedule(EventSchedule $eventSchedule): self
    {
        $model = new self();
        $model->name = $eventSchedule->getName();
        $model->description = $eventSchedule->getDescription();
        $model->owner = $eventSchedule->getOwner();
        $model->templates = $eventSchedule->getTemplates();

        return $model;
    }
}
