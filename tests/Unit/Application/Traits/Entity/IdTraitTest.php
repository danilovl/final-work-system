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

use App\Application\Traits\Entity\IdTrait;
use PHPUnit\Framework\TestCase;

class IdTraitTest extends TestCase
{
    public function testId(): void
    {
        $class = new class() {
            use IdTrait;
        };

        $class->setId(1);
        $this->assertSame(1, $class->getId());

        $class = clone $class;

        $this->assertSame(0, $class->getId());
    }
}
