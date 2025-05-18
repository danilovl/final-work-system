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

namespace Application\Helper;

use App\Application\Helper\ArrayMapHelper;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class ArrayMapHelperTest extends TestCase
{
    public function testGetObjectsIds(): void
    {
        $object1 = new class() {
            public function getId(): int
            {
                return 1;
            }
        };

        $object2 = new class() {
            public function getId(): int
            {
                return 2;
            }
        };

        $objects = [$object1, $object2];

        $ids = ArrayMapHelper::getObjectsIds($objects);

        $this->assertEquals([1, 2], $ids);
    }

    public function testGetObjectsIdsEmptyArray(): void
    {
        $objects = [];

        $ids = ArrayMapHelper::getObjectsIds($objects);

        $this->assertEquals([], $ids);
    }

    public function testGetObjectsIdsException(): void
    {
        $object1 = new class() {
            public function getId(): int
            {
                return 1;
            }
        };

        $object2 = new class () {};

        $objects = [$object1, $object2];

        $this->expectException(InvalidArgumentException::class);

        ArrayMapHelper::getObjectsIds($objects);
    }
}
