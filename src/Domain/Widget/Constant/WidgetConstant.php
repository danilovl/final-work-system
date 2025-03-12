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

namespace App\Domain\Widget\Constant;

enum WidgetConstant: string
{
    case UNREAD_CONVERSATION_MESSAGE = 'unread_conversation_message';
    case UNREAD_SYSTEM_EVENT = 'unread_system_event';
    case LOCALES = 'locales';
    case MENU = 'menu';
    case GROUP_TOP_NAV = 'top_nav';
}
