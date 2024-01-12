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

namespace App\Application\Helper;

use App\Domain\EventParticipant\Entity\EventParticipant;
use Collator;
use Symfony\Component\Yaml\Yaml;

class SortFunctionHelper
{
    private const PATH_CZECH_CHARS = __DIR__ . '/../../../config/project/sorter_chars.yaml';

    public static function usortCzechArray(array &$array): void
    {
        $collator = new Collator('cs_CZ.UTF-8');

        usort($array, static function (mixed $first, mixed $second) use ($collator): int {
            $f = (string) $first;
            $s = (string) $second;

            return (int) $collator->compare($f, $s);
        });
    }

    public static function sortCzechChars(string $a, string $b): int
    {
        /** @var string $a */
        $a = str_replace(['Ch', 'ch'], ['HZZ', 'hzz'], $a);
        /** @var string $b */
        $b = str_replace(['Ch', 'ch'], ['HZZ', 'hzz'], $b);
        /** @var array $czechChars */
        $czechChars = Yaml::parse(file_get_contents(self::PATH_CZECH_CHARS))['czech'] ?? [];

        $a = strtr($a, $czechChars);
        $b = strtr($b, $czechChars);

        return strnatcasecmp($a, $b);
    }

    public static function eventParticipantSort(array &$eventParticipantArray): void
    {
        usort($eventParticipantArray, static function (EventParticipant $first, EventParticipant $second): int {
            /** @var string $f */
            $f = iconv('UTF-8', 'ASCII//TRANSLIT', $first->getUserMust()->getFullNameDegree());
            /** @var string $s */
            $s = iconv('UTF-8', 'ASCII//TRANSLIT', $second->getUserMust()->getFullNameDegree());

            return self::sortCzechChars($f, $s);
        });
    }
}
