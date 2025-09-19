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

namespace App\Domain\User\Helper;

use App\Domain\User\Entity\User;
use Collator;
use Webmozart\Assert\Assert;

class SortFunctionHelper
{
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
}
