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

use App\Application\Traits\Entity\PublicAbleTrait;
use DateTime;
use PHPUnit\Framework\TestCase;

class PublicAbleTraitTest extends TestCase
{
    public function testIsOwner(): void
    {
        $class = new class {
            use PublicAbleTrait;
        };

        $date = new DateTime;

        $class->setPublicFrom($date);
        $this->assertSame($date, $class->getPublicFrom());

        $class->setPublicTo($date);
        $this->assertSame($date, $class->getPublicTo());
    }
}
