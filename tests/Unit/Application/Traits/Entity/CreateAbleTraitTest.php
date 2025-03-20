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

use App\Application\Traits\Entity\CreateAbleTrait;
use DateTime;
use PHPUnit\Framework\TestCase;

class CreateAbleTraitTest extends TestCase
{
    public function testActive(): void
    {
        $class = new class() {
            use CreateAbleTrait;
        };

        $date = new DateTime;

        $class->setCreatedAt($date);
        $this->assertSame($date, $class->getCreatedAt());

        $class->createAblePrePersist();

        $this->assertNotSame($date, $class->getCreatedAt());
    }
}
