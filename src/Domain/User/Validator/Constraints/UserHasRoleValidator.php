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

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stringable;
use Symfony\Component\Validator\{Constraint, ConstraintValidator};
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UserHasRoleValidator extends ConstraintValidator
{
    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UserHasRole) {
            throw new UnexpectedTypeException($constraint, UserHasRole::class);
        }

        if ($value === null) {
            return;
        }

        /** @var UserRepository $repository */
        $repository = $this->entityManager->getRepository(User::class);
        $qb = $repository->allByUserRole($constraint->role, $constraint->enabled);
        
        $rootAlias = $qb->getRootAliases()[0];
        $qb->andWhere("{$rootAlias}.id = :id")
            ->setParameter('id', $value);

        $count = (int) $qb->select("count({$rootAlias}.id)")
            ->getQuery()
            ->getSingleScalarResult();

        if ($count === 0) {
            $valueString = is_scalar($value) || $value instanceof Stringable ? (string) $value : '';

            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $valueString)
                ->setParameter('{{ role }}', $constraint->role)
                ->addViolation();
        }
    }
}
