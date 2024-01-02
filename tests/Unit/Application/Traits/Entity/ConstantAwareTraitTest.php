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

use App\Application\Traits\Entity\ConstantAwareTrait;
use PHPUnit\Framework\TestCase;

class ConstantAwareTraitTest extends TestCase
{
    public function testActive(): void
    {
        $class = new class {
            use ConstantAwareTrait;
        };

        $class->setConstant('test');
        $this->assertSame('test', $class->getConstant());
    }
}
