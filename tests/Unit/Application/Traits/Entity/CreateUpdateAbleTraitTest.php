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

use App\Application\Traits\Entity\CreateUpdateAbleTrait;
use DateTime;
use PHPUnit\Framework\TestCase;

class CreateUpdateAbleTraitTest extends TestCase
{
    public function testDate(): void
    {
        $class = new class {
            use CreateUpdateAbleTrait;
        };

        $date = new DateTime;

        $class->setCreatedAt($date);
        $this->assertSame($date, $class->getCreatedAt());

        $class->setUpdatedAt($date);
        $this->assertSame($date, $class->getUpdatedAt());

        $class->createUpdateAblePrePersist();
        $class->createUpdateAblePreUpdate();

        $this->assertNotSame($date, $class->getCreatedAt());
        $this->assertNotSame($date, $class->getUpdatedAt());
    }
}
