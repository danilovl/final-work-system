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

namespace App\Domain\EventSchedule\Model;

use App\Application\Traits\Model\SimpleInformationTrait;
use App\Domain\EventSchedule\Entity\EventSchedule;
use App\Domain\User\Entity\User;
use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection};

class EventScheduleModel
{
    use SimpleInformationTrait;

    public ?User $owner = null;
    public ?Collection $templates = null;

    public function __construct()
    {
        $this->templates = new ArrayCollection;
    }

    public static function fromEventSchedule(EventSchedule $eventSchedule): self
    {
        $model = new self;
        $model->name = $eventSchedule->getName();
        $model->description = $eventSchedule->getDescription();
        $model->owner = $eventSchedule->getOwner();
        $model->templates = $eventSchedule->getTemplates();

        return $model;
    }
}
