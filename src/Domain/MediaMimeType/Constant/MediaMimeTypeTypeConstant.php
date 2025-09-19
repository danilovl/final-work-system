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

namespace App\Domain\MediaMimeType\Constant;

enum MediaMimeTypeTypeConstant
{
    final public const array JPG = [
        'extension' => 'jpg',
        'mimeType' => 'image/jpeg',
    ];

    final public const array PNG = [
        'extension' => 'png',
        'mimeType' => 'image/png',
    ];
}
