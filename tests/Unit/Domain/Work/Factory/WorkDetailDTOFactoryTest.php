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

namespace App\Tests\Unit\Domain\Work\Factory;

use App\Tests\Mock\Domain\Work\WorkApiDTOTraitMock;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class WorkDetailDTOFactoryTest extends TestCase
{
    use WorkApiDTOTraitMock;

    public function testCreate(): void
    {
        $dto = $this->createWorkDetailDTOFactory()->create($this->createWork());

        $this->assertSame(11, $dto->id);
        $this->assertSame('Thesis', $dto->title);
        $this->assertSame('2026-06-30 14:05:09', $dto->deadline, 'API keeps the Y-m-d H:i:s format');
        $this->assertNull($dto->deadlineProgram);
        $this->assertSame(37, $dto->author->id);
        $this->assertSame(36, $dto->supervisor->id);
        $this->assertNull($dto->opponent);
        $this->assertNull($dto->consultant);
        $this->assertSame('T', $dto->type->shortcut);
        $this->assertSame('#000', $dto->status->color);
    }

    public function testKeysOfFormerApiOutputArePresent(): void
    {
        $work = $this->createWork();
        $work->setDeadlineProgram(new DateTimeImmutable('2026-05-01 08:00:00'));
        $work->setOpponent($this->createUser(11));

        $dto = $this->createWorkDetailDTOFactory()->create($work);

        $this->assertSame(
            ['id', 'title', 'type', 'status', 'deadline', 'deadlineProgram', 'author', 'supervisor', 'opponent', 'consultant'],
            array_keys(get_object_vars($dto))
        );
        $this->assertSame('2026-05-01 08:00:00', $dto->deadlineProgram);
        $this->assertNotNull($dto->opponent);
        $this->assertSame(
            ['id', 'username', 'firstname', 'lastname', 'fullName', 'email', 'degreeBefore', 'degreeAfter', 'roles'],
            array_keys(get_object_vars($dto->opponent))
        );
    }
}
