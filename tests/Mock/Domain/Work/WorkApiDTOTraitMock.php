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

namespace App\Tests\Mock\Domain\Work;

use App\Domain\User\DTO\Api\UserDTO;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Factory\WorkDetailDTOFactory;
use App\Domain\WorkStatus\DTO\Api\WorkStatusDTO;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Domain\WorkType\DTO\Api\WorkTypeDTO;
use App\Domain\WorkType\Entity\WorkType;
use Danilovl\ObjectDtoMapper\Service\ObjectToDtoMapperInterface;
use DateTime;
use LogicException;

/**
 * Builds WorkDetailDTOFactory with a stubbed ObjectToDtoMapper and simple Work/User entities.
 */
trait WorkApiDTOTraitMock
{
    protected function createWorkDetailDTOFactory(): WorkDetailDTOFactory
    {
        $mapper = $this->createStub(ObjectToDtoMapperInterface::class);
        $mapper->method('map')->willReturnCallback(static function (object $source, string $target): object {
            return match (true) {
                $target === UserDTO::class && $source instanceof User => new UserDTO(
                    id: $source->getId(),
                    username: 'user' . $source->getId(),
                    firstname: 'First',
                    lastname: 'Last',
                    fullName: 'Last First',
                    email: 'user@example.com',
                    degreeBefore: null,
                    degreeAfter: null
                ),
                $target === WorkTypeDTO::class => new WorkTypeDTO(id: 3, name: 'Type', description: null, shortcut: 'T'),
                $target === WorkStatusDTO::class => new WorkStatusDTO(id: 1, name: 'Active', description: null, color: '#000'),
                default => throw new LogicException($target)
            };
        });

        return new WorkDetailDTOFactory($mapper);
    }

    protected function createUser(int $id): User
    {
        $user = new User;
        $user->setId($id);

        return $user;
    }

    protected function createWork(): Work
    {
        $work = new Work;
        $work->setId(11);
        $work->setTitle('Thesis');
        $work->setType(new WorkType);
        $work->setStatus(new WorkStatus);
        $work->setDeadline(new DateTime('2026-06-30 14:05:09'));
        $work->setDeadlineProgram(null);
        $work->setAuthor($this->createUser(37));
        $work->setSupervisor($this->createUser(36));

        return $work;
    }
}
