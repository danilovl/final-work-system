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

namespace App\Domain\User\Validator\Constraints;

use App\Domain\User\Facade\UserFacade;
use Stringable;
use Symfony\Component\Validator\{Constraint, ConstraintValidator};
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UserHasRoleValidator extends ConstraintValidator
{
    public function __construct(private readonly UserFacade $userFacade) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UserHasRole) {
            throw new UnexpectedTypeException($constraint, UserHasRole::class);
        }

        if ($value === null) {
            return;
        }

        $isValidId = is_int($value) || is_string($value);
        $hasRole = $isValidId && $this->userFacade->hasUserRole(
            id: $value,
            role: $constraint->role,
            enabled: $constraint->enabled
        );

        if (!$hasRole) {
            $valueString = is_scalar($value) || $value instanceof Stringable ? (string) $value : '';

            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $valueString)
                ->setParameter('{{ role }}', $constraint->role)
                ->addViolation();
        }
    }
}
