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

namespace FinalWork\FinalWorkBundle\Constant;

class FileSizeConstant
{
    public const B = 'B';
    public const KB = 'KB';
    public const MB = 'MB';
    public const GB = 'GB';
    public const TB = 'TB';
    public const PB = 'PB';
    public const EB = 'EB';
    public const ZB = 'ZB';
    public const YB = 'YB';

    /**
     * @var string[]
     */
    public const FILE_SIZES = [
        self::B,
        self::KB,
        self::MB,
        self::GB,
        self::TB,
        self::PB,
        self::EB,
        self::ZB,
        self::YB
    ];
}