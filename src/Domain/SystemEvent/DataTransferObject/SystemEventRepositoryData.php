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

namespace App\Domain\SystemEvent\DataTransferObject;

use App\Application\DataTransferObject\BaseDataTransferObject;
use App\Domain\User\Entity\User;

class SystemEventRepositoryData extends BaseDataTransferObject
{
    public ?User $recipient = null;
    public ?bool $viewed = null;
    public ?int $limit = null;
    public ?int $offset = null;
}
