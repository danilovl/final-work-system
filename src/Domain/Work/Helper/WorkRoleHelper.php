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

use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;

class WorkRoleHelper
{
    public static function hasAccessToWork(Work $work, User $user): bool
    {
        return self::isAuthor($work, $user) || self::isSupervisor($work, $user) || self::isOpponent($work, $user) || self::isConsultant($work, $user);
    }

    public static function isAuthorSupervisor(Work $work, User $user): bool
    {
        return self::isAuthor($work, $user) || self::isSupervisor($work, $user);
    }

    public static function isAuthor(Work $work, User $user): bool
    {
        return $work->getAuthor()->getId() === $user->getId();
    }

    public static function isSupervisor(Work $work, User $user): bool
    {
        return $work->getSupervisor()->getId() === $user->getId();
    }

    public static function isOpponent(Work $work, User $user): bool
    {
        return $work->getOpponent()?->getId() === $user->getId();
    }

    public static function isConsultant(Work $work, User $user): bool
    {
        return $work->getConsultant()?->getId() === $user->getId();
    }
}
