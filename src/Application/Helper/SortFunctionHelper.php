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

namespace App\Application\Helper;

use Collator;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Yaml\Yaml;

class SortFunctionHelper
{
    private const string PATH_CZECH_CHARS = __DIR__ . '/../../../config/project/sorter_chars.yaml';

    /** @var array<string, string>|null */
    private static ?array $czechChars = null;

    public static function usortCzechArray(array &$array): void
    {
        $collator = new Collator('cs_CZ.UTF-8');

        usort($array, static function (string $first, string $second) use ($collator): int {
            return (int) $collator->compare($first, $second);
        });
    }

    public static function sortCzechChars(string $a, string $b): int
    {
        /** @var string $a */
        $a = str_replace(['Ch', 'ch'], ['HZZ', 'hzz'], $a);
        /** @var string $b */
        $b = str_replace(['Ch', 'ch'], ['HZZ', 'hzz'], $b);

        $czechChars = self::getCzechChars();

        $a = strtr($a, $czechChars);
        $b = strtr($b, $czechChars);

        return strnatcasecmp($a, $b);
    }

    /**
     * @return array<string, string>
     */
    private static function getCzechChars(): array
    {
        if (self::$czechChars !== null) {
            return self::$czechChars;
        }

        $file = new File(self::PATH_CZECH_CHARS);

        /** @var array<string, array<string, string>> $sorterChars */
        $sorterChars = Yaml::parse($file->getContent());
        self::$czechChars = $sorterChars['czech'] ?? [];

        return self::$czechChars;
    }
}
