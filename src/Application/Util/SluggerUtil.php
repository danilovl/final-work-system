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

namespace App\Application\Util;

class SluggerUtil
{
    public function slugify(string $string): string
    {
        /** @var string $string */
        $string = preg_replace('~\s+~', '-', mb_strtolower(trim(strip_tags($string)), 'UTF-8'));

        return $string;
    }
}
