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
use App\Entity\MediaType;

class MediaData extends BaseDataTransferObject
{
    public mixed $users = null;
    public ?bool $active = null;
    public MediaType|iterable|null $type = null;
    public array|null $criteria = null;
}
