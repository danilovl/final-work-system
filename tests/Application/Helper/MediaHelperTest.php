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

namespace App\Tests\Application\Helper;

use App\Domain\Media\Helper\MediaHelper;
use App\Domain\MediaMimeType\Entity\MediaMimeType;
use PHPUnit\Framework\TestCase;

class MediaHelperTest extends TestCase
{
    public function testGenerateMediaNameByType(): void
    {
        $mediaMimeType = new MediaMimeType;
        $mediaMimeType->setExtension('png');

        $result = MediaHelper::generateMediaNameByType($mediaMimeType);

        $this->assertTrue(str_contains($result, '.png'));
    }
}
