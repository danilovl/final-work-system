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

namespace App\Application\Constant;

enum DateFormatConstant: string
{
    case DATABASE = 'Y-m-d H:i:s';
    case WIDGET_SINGLE_TEXT_DATE = 'yyyy-MM-dd';
    case WIDGET_SINGLE_TEXT_DATE_TIME = 'yyyy-MM-dd HH:mm';
    case DATE = 'Y-m-d';
    case TIME = 'H:i:s';
    case DATE_TIME = 'Y-m-d H:i';
}
