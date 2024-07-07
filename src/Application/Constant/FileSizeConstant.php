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

enum FileSizeConstant: string
{
    case B = 'B';
    case KB = 'KB';
    case MB = 'MB';
    case GB = 'GB';
    case TB = 'TB';
    case PB = 'PB';
    case EB = 'EB';
    case ZB = 'ZB';
    case YB = 'YB';

    /**
     * @var string[]
     */
    public const array FILE_SIZES = [
        self::B->value,
        self::KB->value,
        self::MB->value,
        self::GB->value,
        self::TB->value,
        self::PB->value,
        self::EB->value,
        self::ZB->value,
        self::YB->value
    ];
}
