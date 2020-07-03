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

namespace App\Helper;

use Collator;
use App\Entity\EventParticipant;

class SortFunctionHelper
{
    public static function usortCzechArray(array &$array): void
    {
        $collator = new Collator('cs_CZ.UTF-8');

        usort($array, static function ($first, $second) use ($collator) {
            $f = (string)$first;
            $s = (string)$second;

            return $collator->compare($f, $s);
        });
    }

    public static function sortCzechChars($a, $b): int
    {
        $a = str_replace(['Ch', 'ch'], ['HZZ', 'hzz'], $a);
        $b = str_replace(['Ch', 'ch'], ['HZZ', 'hzz'], $b);
        static $czechChars = [
            'A' => 'A', 'Á' => 'AZ', 'B' => 'B', 'C' => 'C', 'Č' => 'CZ',
            'D' => 'D', 'Ď' => 'DZ', 'E' => 'E', 'É' => 'EZ', 'Ě' => 'EZZ',
            'F' => 'F', 'G' => 'G', 'H' => 'H', 'I' => 'I', 'Í' => 'IZ', 'J' => 'J',
            'K' => 'K', 'L' => 'L', 'M' => 'M', 'N' => 'N', 'Ň' => 'NZ',
            'O' => 'O', 'Ó' => 'OZ', 'P' => 'P', 'Q' => 'Q', 'R' => 'R',
            'Ř' => 'RZ', 'S' => 'S', 'Š' => 'SZ', 'T' => 'T', 'Ť' => 'TZ',
            'U' => 'U', 'Ú' => 'UZ', 'Ů' => 'UZZ', 'V' => 'V', 'W' => 'W',
            'X' => 'X', 'Y' => 'Y', 'Ý' => 'YZ', 'Z' => 'Z', 'Ž' => 'ZZ',
            'a' => 'a', 'á' => 'az', 'b' => 'b', 'c' => 'c', 'č' => 'cz',
            'd' => 'd', 'ď' => 'dz', 'e' => 'e', 'é' => 'ez', 'ě' => 'ezz',
            'f' => 'f', 'g' => 'g', 'h' => 'h', 'i' => 'i', 'í' => 'iz', 'j' => 'j',
            'k' => 'k', 'l' => 'l', 'm' => 'm', 'n' => 'n', 'ň' => 'nz', 'o' => 'o',
            'ó' => 'oz', 'p' => 'p', 'q' => 'q', 'r' => 'r', 'ř' => 'rz', 's' => 's',
            'š' => 'sz', 't' => 't', 'ť' => 'tz', 'u' => 'u', 'ú' => 'uz', 'ů' => 'uzz',
            'v' => 'v', 'w' => 'w', 'x' => 'x', 'y' => 'y', 'ý' => 'yz', 'z' => 'z',
            'ž' => 'zz'
        ];

        $A = strtr($a, $czechChars);
        $B = strtr($b, $czechChars);

        return strnatcasecmp($A, $B);
    }

    public static function eventParticipantSort(array &$eventParticipantArray): void
    {
        usort($eventParticipantArray, function ($first, $second) {
            /** @var EventParticipant $a */
            /** @var EventParticipant $b */
            $f = iconv('UTF-8', 'ASCII//TRANSLIT', (string)$first->getUser());
            $s = iconv('UTF-8', 'ASCII//TRANSLIT', (string)$second->getUser());

            return self::sortCzechChars($f, $s);
        });
    }
}
