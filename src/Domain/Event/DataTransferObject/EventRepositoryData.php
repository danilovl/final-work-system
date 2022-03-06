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

namespace App\Domain\Event\DataTransferObject;

use App\Application\DataTransferObject\BaseDataTransferObject;
use App\Domain\EventType\Entity\EventType;
use App\Domain\User\Entity\User;
use DateTime;

class EventRepositoryData extends BaseDataTransferObject
{
    public ?User $user = null;
    public ?DateTime $startDate = null;
    public ?DateTime $endDate = null;
    public ?EventType $eventType = null;
}
