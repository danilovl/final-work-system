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

namespace App\Domain\WorkCategory\Validator\Constraints;

use App\Application\Helper\FunctionHelper;
use App\Domain\User\Service\UserService;
use App\Domain\WorkCategory\Facade\WorkCategoryFacade;
use Symfony\Component\Validator\{
    Constraint,
    ConstraintValidator
};
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class WorkCategoryOwnerValidator extends ConstraintValidator
{
    public function __construct(
        private readonly WorkCategoryFacade $workCategoryFacade,
        private readonly UserService $userService
    ) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof WorkCategoryOwner) {
            throw new UnexpectedTypeException($constraint, WorkCategoryOwner::class);
        }

        if ($value === null || (is_array($value) && count($value) === 0)) {
            return;
        }

        $user = $this->userService->getUser();
        /** @var array<int|string> $arrayValue */
        $arrayValue = (array) $value;
        $uniqueIds = array_unique($arrayValue);
        $ids = array_values($uniqueIds);
        /** @var int[] $ids */
        $ids = array_map(static fn (mixed $id): int => (int) $id, $ids);

        $categories = $this->workCategoryFacade->findByOwnerAndIds($user, $ids);
        $existingIds = [];
        foreach ($categories as $category) {
            $existingIds[] = $category->getId();
        }

        $isEqual = FunctionHelper::compareSimpleTwoArray($ids, $existingIds);
        if (!$isEqual) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
