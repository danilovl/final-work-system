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

use App\Constant\{
    UserRoleConstant,
    WorkUserTypeConstant
};

class UserHelper
{
    public static function getRealRoleName($role): ?string
    {
        $rolesName = [
            UserRoleConstant::STUDENT => 'student',
            UserRoleConstant::SUPERVISOR => WorkUserTypeConstant::SUPERVISOR,
            UserRoleConstant::OPPONENT => WorkUserTypeConstant::OPPONENT,
            UserRoleConstant::CONSULTANT => WorkUserTypeConstant::CONSULTANT
        ];

        return $rolesName[$role] ?? null;
    }
}
 