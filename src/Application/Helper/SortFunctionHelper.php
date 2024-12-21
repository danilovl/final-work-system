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
use App\Domain\User\Entity\User;
use Collator;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

class SortFunctionHelper
{
    private const string PATH_CZECH_CHARS = __DIR__ . '/../../../config/project/sorter_chars.yaml';

    public static function usortCzechArray(array &$array): void
    {
        $collator = new Collator('cs_CZ.UTF-8');

        usort($array, static function (string $first, string $second) use ($collator): int {
            return (int) $collator->compare($first, $second);
        });
    }

    /**
     * @param User[] $array
     */
    public static function usortCzechUserArray(array &$array): void
    {
        Assert::allIsInstanceOf($array, User::class);

        $collator = new Collator('cs_CZ.UTF-8');

        usort($array, static function (User $first, User $second) use ($collator): int {
            $f = $first->getFullNameDegree();
            $s = $second->getFullNameDegree();

            return (int) $collator->compare($f, $s);
        });
    }

    public static function sortCzechChars(string $a, string $b): int
    {
        /** @var string $a */
        $a = str_replace(['Ch', 'ch'], ['HZZ', 'hzz'], $a);
        /** @var string $b */
        $b = str_replace(['Ch', 'ch'], ['HZZ', 'hzz'], $b);

        $file = new File(self::PATH_CZECH_CHARS);

        /** @var array<string, array> $sorterChars */
        $sorterChars = Yaml::parse($file->getContent());
        $czechChars = $sorterChars['czech'] ?? [];

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
