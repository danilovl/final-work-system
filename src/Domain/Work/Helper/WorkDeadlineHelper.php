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

namespace App\Domain\Work\Helper;

use App\Domain\Work\Entity\Work;
use DateTimeImmutable;

class WorkDeadlineHelper
{
    public static function getDeadlineDays(Work $work): int
    {
        $now = new DateTimeImmutable;
        $diff = (int) $work->getDeadline()->diff($now)->format('%a');

        return $work->getDeadline()->diff($now)->invert ? $diff : -$diff;
    }
}
