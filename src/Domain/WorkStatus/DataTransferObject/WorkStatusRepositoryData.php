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

namespace App\Domain\WorkStatus\DataTransferObject;

use App\Application\DataTransferObject\BaseDataTransferObject;
use App\Domain\User\Entity\User;
use App\Domain\WorkStatus\Entity\WorkStatus;

class WorkStatusRepositoryData extends BaseDataTransferObject
{
    public ?User $user = null;
    public ?User $supervisor = null;
    public ?string $type = null;
    public WorkStatus|iterable|null $workStatus = null;
}
