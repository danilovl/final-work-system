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

namespace App\Tests\Unit\Domain\Work\Entity;

use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Domain\WorkType\Entity\WorkType;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class WorkTest extends TestCase
{
    #[DataProvider('gettersAndSettersProvider')]
    public function testGettersAndSetters(
        mixed $value,
        string $set,
        string $get
    ): void {
        $entity = new class extends Work { };
        $entity->$set($value);

        $this->assertEquals($value, $entity->$get());
    }

    public static function gettersAndSettersProvider(): Generator
    {
        yield [1, 'setId', 'getId'];
        yield ['Work title', 'setTitle', 'getTitle'];
        yield ['Shortcut', 'setShortcut', 'getShortcut'];
        yield [null, 'setShortcut', 'getShortcut'];
        yield [new ArrayCollection, 'setTasks', 'getTasks'];
        yield [new DateTime, 'setDeadline', 'getDeadline'];
        yield [new DateTime, 'setDeadlineProgram', 'getDeadlineProgram'];
        yield [null, 'setDeadlineProgram', 'getDeadlineProgram'];
        yield [new ArrayCollection, 'setTasks', 'getActiveTask'];
        yield [new ArrayCollection, 'setCategories', 'getCategories'];
        yield [new class extends WorkStatus { }, 'setStatus', 'getStatus'];
        yield [new class extends WorkType { }, 'setType', 'getType'];
        yield [new class extends User { }, 'setConsultant', 'getConsultant'];
        yield [null, 'setConsultant', 'getConsultant'];
        yield [new class extends User { }, 'setOpponent', 'getOpponent'];
        yield [null, 'setOpponent', 'getOpponent'];
        yield [new class extends User { }, 'setSupervisor', 'getSupervisor'];
        yield [new class extends User { }, 'setAuthor', 'getAuthor'];
    }
}
