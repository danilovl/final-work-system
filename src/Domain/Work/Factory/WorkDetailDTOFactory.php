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

namespace App\Domain\Work\Factory;

use App\Infrastructure\Constant\DateFormatConstant;
use App\Domain\User\DTO\Api\UserDTO;
use App\Domain\User\Entity\User;
use App\Domain\Work\DTO\Api\WorkDetailDTO;
use App\Domain\Work\Entity\Work;
use App\Domain\WorkStatus\DTO\Api\WorkStatusDTO;
use App\Domain\WorkType\DTO\Api\WorkTypeDTO;
use Danilovl\ObjectDtoMapper\Service\ObjectToDtoMapperInterface;

readonly class WorkDetailDTOFactory
{
    public function __construct(private ObjectToDtoMapperInterface $objectToDtoMapper) {}

    public function create(Work $work): WorkDetailDTO
    {
        return new WorkDetailDTO(
            id: $work->getId(),
            title: $work->getTitle(),
            type: $this->objectToDtoMapper->map($work->getType(), WorkTypeDTO::class),
            status: $this->objectToDtoMapper->map($work->getStatus(), WorkStatusDTO::class),
            deadline: $work->getDeadline()->format(DateFormatConstant::DATABASE->value),
            deadlineProgram: $work->getDeadlineProgram()?->format(DateFormatConstant::DATABASE->value),
            author: $this->createUser($work->getAuthor()),
            supervisor: $this->createUser($work->getSupervisor()),
            opponent: $work->getOpponent() !== null ? $this->createUser($work->getOpponent()) : null,
            consultant: $work->getConsultant() !== null ? $this->createUser($work->getConsultant()) : null
        );
    }

    public function createUser(User $user): UserDTO
    {
        return $this->objectToDtoMapper->map($user, UserDTO::class);
    }
}
