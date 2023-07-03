<?php declare(strict_types=1);

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
