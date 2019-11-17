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

namespace FinalWork\FinalWorkBundle\Helper;

use FinalWork\FinalWorkBundle\Constant\{
    UserRoleConstant,
    WorkUserTypeConstant
};

class UserHelper
{
    /**
     * @param $role
     * @return string|null
     */
    public static function getRealRoleName($role): ?string
    {
        $rolesName = [
            UserRoleConstant::STUDENT => 'student',
            UserRoleConstant::SUPERVISOR => WorkUserTypeConstant::SUPERVISOR,
            UserRoleConstant::OPPONENT => WorkUserTypeConstant::OPPONENT,
            UserRoleConstant::CONSULTANT => WorkUserTypeConstant::OPPONENT
        ];

        return $rolesName[$role] ?? null;
    }
}
 