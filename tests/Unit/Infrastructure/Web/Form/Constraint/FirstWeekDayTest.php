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

namespace App\Tests\Unit\Infrastructure\Web\Form\Constraint;

use App\Infrastructure\Web\Form\Constraint\FirstWeekDay;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class FirstWeekDayTest extends TestCase
{
    public function testGetTargets(): void
    {
        $firstWeekDay = new FirstWeekDay;

        $this->assertSame(Constraint::CLASS_CONSTRAINT, $firstWeekDay->getTargets());
    }
}
