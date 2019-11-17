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
use FinalWork\FinalWorkBundle\Model\BaseModelFactory;
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};

class EventScheduleTemplateFactory extends BaseModelFactory
{
    /**
     * @param EventScheduleTemplateModel $eventScheduleTemplateModel
     * @param EventScheduleTemplate|null $eventScheduleTemplate
     * @return EventScheduleTemplate
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createFromModel(
        EventScheduleTemplateModel $eventScheduleTemplateModel,
        ?EventScheduleTemplate $eventScheduleTemplate
    ): EventScheduleTemplate {
        $eventScheduleTemplate = $eventScheduleTemplate ?? new EventScheduleTemplate;
        $eventScheduleTemplate = $this->fromModel($eventScheduleTemplate, $eventScheduleTemplateModel);

        $this->em->persist($eventScheduleTemplate);
        $this->em->flush();

        return $eventScheduleTemplate;
    }

    /**
     * @param EventScheduleTemplate $eventScheduleTemplate
     * @param EventScheduleTemplateModel $eventScheduleTemplateModel
     * @return EventScheduleTemplate
     */
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
