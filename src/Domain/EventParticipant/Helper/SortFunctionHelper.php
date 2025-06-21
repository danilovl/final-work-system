<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Domain\EventParticipant\Helper;

use App\Application\Helper\SortFunctionHelper as BaseSortFunctionHelper;
use App\Domain\EventParticipant\Entity\EventParticipant;

class SortFunctionHelper
{
    public static function eventParticipantSort(array &$eventParticipantArray): void
    {
        usort($eventParticipantArray, static function (EventParticipant $first, EventParticipant $second): int {
            /** @var string $f */
            $f = iconv('UTF-8', 'ASCII//TRANSLIT', $first->getUserMust()->getFullNameDegree());
            /** @var string $s */
            $s = iconv('UTF-8', 'ASCII//TRANSLIT', $second->getUserMust()->getFullNameDegree());

            return BaseSortFunctionHelper::sortCzechChars($f, $s);
        });
    }
}
