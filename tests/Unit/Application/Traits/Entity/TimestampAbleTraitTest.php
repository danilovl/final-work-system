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

use App\Application\Traits\Entity\TimestampAbleTrait;
use DateTime;
use PHPUnit\Framework\TestCase;

class TimestampAbleTraitTest extends TestCase
{
    public function testIsOwner(): void
    {
        $class = new class {
            use TimestampAbleTrait;
        };

        $date = new DateTime;

        $class->setUpdatedAt($date);
        $this->assertSame($date, $class->getUpdatedAt());

        $class->setCreatedAt($date);
        $this->assertSame($date, $class->getCreatedAt());

        $class->timestampAblePrePersist();
        $class->timestampAblePreUpdate();

        $this->assertNotSame($date, $class->getUpdatedAt());
        $this->assertNotSame($date, $class->getCreatedAt());
    }
}
