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

namespace App\Constant;

use ReflectionClass;
use ReflectionException;

class UserRoleConstant
{
    public const SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    public const ADMIN = 'ROLE_ADMIN';
    public const STUDENT = 'ROLE_STUDENT';
    public const SUPERVISOR = 'ROLE_SUPERVISOR';
    public const OPPONENT = 'ROLE_OPPONENT';
    public const CONSULTANT = 'ROLE_CONSULTANT';
    public const GUEST = 'ROLE_GUEST';
    public const USER = 'ROLE_USER';

    /**
     * @return array
     * @throws ReflectionException
     */
    public static function getConstants(): array
    {
        $oClass = new ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}