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
use App\Model\User\Entity\User;
use App\Model\WorkStatus\Entity\WorkStatus;

class WorkData extends BaseDataTransferObject
{
    public ?User $user = null;
    public ?User $supervisor = null;
    public ?string $type = null;
    public WorkStatus|iterable|null $workStatus = null;
}
