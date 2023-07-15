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

namespace App\Domain\User\Helper;

use App\Domain\User\Constant\UserRoleConstant;
use App\Domain\Work\Constant\WorkUserTypeConstant;

class UserHelper
{
    public static function getRealRoleName(string $role): ?string
    {
        $rolesName = [
            UserRoleConstant::STUDENT->value => 'student',
            UserRoleConstant::SUPERVISOR->value => WorkUserTypeConstant::SUPERVISOR->value,
            UserRoleConstant::OPPONENT->value => WorkUserTypeConstant::OPPONENT->value,
            UserRoleConstant::CONSULTANT->value => WorkUserTypeConstant::CONSULTANT->value
        ];

        return $rolesName[$role] ?? null;
    }
}
