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

namespace App\Tests\Unit\Domain\Document\Model;

use App\Domain\Document\Model\DocumentSearchModel;
use App\Domain\MediaCategory\Entity\MediaCategory;
use App\Domain\MediaMimeType\Entity\MediaMimeType;
use PHPUnit\Framework\TestCase;

class DocumentSearchModelTest extends TestCase
{
    public function testEmptyModelGivesSameKeysAsFormArray(): void
    {
        $this->assertSame(
            ['name' => null, 'categories' => [], 'mimeType' => []],
            (new DocumentSearchModel)->toArray()
        );
    }

    public function testFilledModel(): void
    {
        $category = new MediaCategory;
        $mimeType = new MediaMimeType;

        $model = new DocumentSearchModel;
        $model->name = 'thesis';
        $model->categories = [$category];
        $model->mimeType = [$mimeType];

        $this->assertSame(
            ['name' => 'thesis', 'categories' => [$category], 'mimeType' => [$mimeType]],
            $model->toArray()
        );
    }
}
