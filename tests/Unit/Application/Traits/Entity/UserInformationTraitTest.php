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

use App\Application\Traits\Entity\UserInformationTrait;
use PHPUnit\Framework\TestCase;

class UserInformationTraitTest extends TestCase
{
    public function testIsOwner(): void
    {
        $class = new class() {
            use UserInformationTrait;
        };

        $class->setFirstName('First');
        $class->setSecondName('Second');
        $class->setDegreeBefore('Degree');
        $class->setDegreeAfter('Degree');

        $this->assertSame('First', $class->getFirstName());
        $this->assertSame('Second', $class->getSecondName());
        $this->assertSame('Degree', $class->getDegreeBefore());
        $this->assertSame('Degree', $class->getDegreeAfter());
    }
}
