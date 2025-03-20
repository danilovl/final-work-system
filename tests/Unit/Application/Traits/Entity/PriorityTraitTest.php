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

use App\Application\Traits\Entity\PriorityTrait;
use PHPUnit\Framework\TestCase;

class PriorityTraitTest extends TestCase
{
    public function testIsOwner(): void
    {
        $class = new class() {
            use PriorityTrait;
        };

        $class->setPriority(10);
        $this->assertSame(10, $class->getPriority());
    }
}
