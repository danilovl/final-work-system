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

namespace App\Utils;

class MomentFormatConverter
{
    private static $formatConvertRules = [
        'yyyy' => 'YYYY', 'yy' => 'YY', 'y' => 'YYYY',
        'dd' => 'DD', 'd' => 'D',
        'EE' => 'ddd', 'EEEEEE' => 'dd',
        'ZZZZZ' => 'Z', 'ZZZ' => 'ZZ',
        '\'T\'' => 'T',
    ];

    public function convert(string $format): string
    {
        return strtr($format, self::$formatConvertRules);
    }
}
