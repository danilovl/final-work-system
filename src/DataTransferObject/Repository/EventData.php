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

namespace App\DataTransferObject\Repository;

use App\DataTransferObject\BaseDataTransferObject;
use App\Entity\{
    User,
    EventType
};
use DateTime;

class EventData extends BaseDataTransferObject
{
    public ?User $user = null;
    public ?DateTime $startDate = null;
    public ?DateTime $endDate = null;
    public ?EventType $eventType = null;
}
