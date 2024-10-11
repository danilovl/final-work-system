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

namespace App\Domain\SystemEvent\Constant;

enum SystemEventGeneratorFolderConstant
{
    final public const string LINK = 'link';
    final public const string TEXT = 'text';

    final public const array FOLDERS = [
        self::LINK,
        self::TEXT
    ];
}
