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

namespace App\Domain\ConversationType\Constant;

enum ConversationTypeConstant: int
{
    case WORK = 1;
    case GROUP = 2;

    public static function tryIdByType(string $type): ?int
    {
        return match ($type) {
            'work' => self::WORK->value,
            'group' => self::GROUP->value,
            default => null
        };
    }
}
