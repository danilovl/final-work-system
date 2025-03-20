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

use App\Application\Traits\Entity\IsReadTrait;
use PHPUnit\Framework\TestCase;

class IsReadTraitTest extends TestCase
{
    public function testIsOwner(): void
    {
        $class = new class() {
            use IsReadTrait;
        };

        $class->setRead(true);
        $this->assertTrue($class->isRead());

        $class->setRead(false);
        $this->assertFalse($class->isRead());
    }
}
