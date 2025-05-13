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
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class CreateUpdateAbleTraitTest extends TestCase
{
    public function testDate(): void
    {
        $class = new class() {
            use CreateUpdateAbleTrait;
        };

        $dateTimeImmutable = new DateTimeImmutable;
        $date = new DateTime;

        $class->setCreatedAt($dateTimeImmutable);
        $this->assertSame($dateTimeImmutable, $class->getCreatedAt());

        $class->setUpdatedAt($date);
        $this->assertSame($date, $class->getUpdatedAt());

        $class->createUpdateAblePrePersist();
        $class->createUpdateAblePreUpdate();

        $this->assertNotSame($dateTimeImmutable, $class->getCreatedAt());
        $this->assertNotSame($date, $class->getUpdatedAt());
    }
}
