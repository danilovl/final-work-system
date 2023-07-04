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

enum MediaTypeConstant: int
{
    case WORK_VERSION = 1;
    case INFORMATION_MATERIAL = 2;
    case USER_PROFILE_IMAGE = 3;
    case ARTICLE = 4;
}
