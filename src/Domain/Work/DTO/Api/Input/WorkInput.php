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

namespace App\Domain\Work\DTO\Api\Input;

use App\Domain\Work\Validator\Constraints\EntityExists;
use App\Domain\WorkCategory\Validator\Constraints\WorkCategoryOwner;
use App\Domain\User\Validator\Constraints\UserHasRole;
use App\Domain\User\Constant\UserRoleConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Domain\WorkType\Entity\WorkType;
use Symfony\Component\Validator\Constraints as Assert;

readonly class WorkInput
{
    public function __construct(
        #[Assert\NotBlank]
        public string $title,
        public ?string $shortcut,
        #[Assert\Positive]
        #[EntityExists(class: WorkStatus::class)]
        public int $statusId,
        #[Assert\Positive]
        #[EntityExists(class: WorkType::class)]
        public int $typeId,
        #[Assert\Positive]
        #[UserHasRole(role: UserRoleConstant::STUDENT->value)]
        public int $authorId,
        #[Assert\Positive]
        #[UserHasRole(role: UserRoleConstant::OPPONENT->value)]
        public ?int $opponentId,
        #[Assert\Positive]
        #[UserHasRole(role: UserRoleConstant::CONSULTANT->value)]
        public ?int $consultantId,
        #[Assert\NotBlank]
        #[Assert\Date]
        public string $deadline,
        #[Assert\Date]
        public ?string $deadlineProgram,
        /** @var int[]|null */
        #[WorkCategoryOwner]
        public ?array $categoriesIds
    ) {}
}
