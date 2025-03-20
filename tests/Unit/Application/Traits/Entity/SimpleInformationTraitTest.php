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

use App\Application\Traits\Entity\SimpleInformationTrait;
use PHPUnit\Framework\TestCase;

class SimpleInformationTraitTest extends TestCase
{
    public function testIsOwner(): void
    {
        $class = new class() {
            use SimpleInformationTrait;
        };

        $class->setName('name');
        $this->assertSame('name', $class->getName());

        $class->setDescription('description');
        $this->assertSame('description', $class->getDescription());
    }
}
