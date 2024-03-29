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

namespace App\Domain\Media\Helper;

use App\Application\Helper\HashHelper;
use App\Domain\MediaMimeType\Entity\MediaMimeType;

class MediaHelper
{
    public static function generateMediaNameByType(MediaMimeType $mediaMimeType): string
    {
        return HashHelper::generateDefaultHash() . '.' . $mediaMimeType->getExtension();
    }
}
