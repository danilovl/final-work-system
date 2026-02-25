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

namespace App\Tests\Unit\Application\Helper;

use App\Application\Helper\CloneHelper;
use App\Tests\Mock\Application\Helper\CloneHelperMock;
use PHPUnit\Framework\TestCase;
use stdClass;

class CloneHelperTest extends TestCase
{
    public function testSimpleCloneObject(): void
    {
        $originalObject = new CloneHelperMock;
        $originalObject->property1 = 'property1';
        $originalObject->property2 = 'property2';

        /** @var CloneHelperMock $clonedObject */
        $clonedObject = CloneHelper::simpleCloneObject($originalObject);

        $this->assertNotSame($originalObject, $clonedObject);
        $this->assertSame($originalObject->property1, $clonedObject->property1);
        $this->assertSame($originalObject->property2, $clonedObject->property2);
    }

    public function testSimpleCloneObjects(): void
    {
        $objects = [
            new stdClass,
            new stdClass,
        ];

        $clonedObjects = CloneHelper::simpleCloneObjects($objects);

        $this->assertSame(count($objects), count($clonedObjects));
        $this->assertNotSame($objects, $clonedObjects);

        foreach ($objects as $key => $originalObject) {
            $clonedObject = $clonedObjects[$key];
            $this->assertNotSame($originalObject, $clonedObject);
        }
    }
}
