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

namespace FinalWork\FinalWorkBundle\Helper;

use FinalWork\FinalWorkBundle\Entity\MediaMimeType;

class MediaHelper
{
    /**
     * @param MediaMimeType $mediaMimeType
     * @return string|null
     */
    public static function generateMediaNameByType(MediaMimeType $mediaMimeType): ?string
    {
        return HashHelper::generateDefaultHash() . '.' . $mediaMimeType->getExtension();
    }
}
