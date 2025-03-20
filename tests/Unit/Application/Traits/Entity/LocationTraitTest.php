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

namespace App\Tests\Unit\Application\Traits\Entity;

use App\Application\Traits\Entity\LocationTrait;
use PHPUnit\Framework\TestCase;

class LocationTraitTest extends TestCase
{
    public function testIsOwner(): void
    {
        $class = new class() {
            use LocationTrait;
        };

        $class->setLongitude(10.0);
        $this->assertSame(10.0, $class->getLongitude());

        $class->setLatitude(12.0);
        $this->assertSame(12.0, $class->getLatitude());
    }
}
