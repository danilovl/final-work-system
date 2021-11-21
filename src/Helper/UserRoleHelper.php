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

use App\Model\User\Entity\User;
use App\Constant\UserRoleConstant;

class UserRoleHelper
{
    public static function isAdmin(User $user): bool
    {
        return self::hasRole($user, UserRoleConstant::ADMIN);
    }

    public static function isAuthorSupervisorOpponent(User $user): bool
    {
        return self::isAuthor($user) || self::isSupervisor($user) || self::isOpponent($user);
    }

    public static function isAuthorSupervisorOpponentConsultant(User $user): bool
    {
        return self::isAuthor($user) || self::isSupervisor($user) || self::isOpponent($user) || self::isConsultant($user);
    }

    public static function isAuthorSupervisor(User $user): bool
    {
        return self::isAuthor($user) || self::isSupervisor($user);
    }

    public static function isAuthor(User $user): bool
    {
        return self::hasRole($user, UserRoleConstant::STUDENT);
    }

    public static function isSupervisor(User $user): bool
    {
        return self::hasRole($user, UserRoleConstant::SUPERVISOR);
    }

    public static function isOpponent(User $user): bool
    {
        return self::hasRole($user, UserRoleConstant::OPPONENT);
    }

    public static function isConsultant(User $user): bool
    {
        return self::hasRole($user, UserRoleConstant::CONSULTANT);
    }

    public static function hasRole(User $user, string $role): bool
    {
        return in_array(strtoupper($role), $user->getRoles(), true);
    }
}
