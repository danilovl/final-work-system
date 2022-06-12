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

enum TabTypeConstant
{
    final public const TAB_TASK = 'task';
    public const TAB_VERSION = 'version';
    public const TAB_EVENT = 'event';
    public const TAB_MESSAGE = 'message';

    /**
     * @var string[]
     */
    public const TABS = [
        self::TAB_TASK,
        self::TAB_VERSION,
        self::TAB_EVENT,
        self::TAB_MESSAGE,
    ];
}