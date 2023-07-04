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

namespace App\Application\Constant;

enum UserRoleConstant: string
{
    case SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    case ADMIN = 'ROLE_ADMIN';
    case STUDENT = 'ROLE_STUDENT';
    case SUPERVISOR = 'ROLE_SUPERVISOR';
    case OPPONENT = 'ROLE_OPPONENT';
    case CONSULTANT = 'ROLE_CONSULTANT';
    case GUEST = 'ROLE_GUEST';
    case USER = 'ROLE_USER';
    case API = 'ROLE_API';
}
