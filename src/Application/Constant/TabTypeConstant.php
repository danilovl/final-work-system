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

enum TabTypeConstant: string
{
    case TAB_TASK = 'task';
    case TAB_VERSION = 'version';
    case TAB_EVENT = 'event';
    case TAB_MESSAGE = 'message';

    /**
     * @var string[]
     */
    public const array TABS = [
        self::TAB_TASK->value,
        self::TAB_VERSION->value,
        self::TAB_EVENT->value,
        self::TAB_MESSAGE->value
    ];
}
