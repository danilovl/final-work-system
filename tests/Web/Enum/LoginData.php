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

namespace App\Tests\Web\Enum;

enum LoginData: string
{
    case FAILED_USERNAME = 'admin';
    case FAILED_PASSWORD = 'adminadmin';

    case STUDENT_USERNAME = 'student';
    case STUDENT_PASSWORD = 'studentstudent';

    case SUPERVISOR_USERNAME = 'supervisor';
    case SUPERVISOR_PASSWORD = 'supervisorsupervisor';
}
