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

namespace App\Domain\Work\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\{Constraint, ConstraintValidator};
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EntityExistsValidator extends ConstraintValidator
{
    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof EntityExists) {
            throw new UnexpectedTypeException($constraint, EntityExists::class);
        }

        if ($value === null || $value === []) {
            return;
        }

        /** @var class-string<object> $entityClass */
        $entityClass = $constraint->class;
        $repository = $this->entityManager->getRepository($entityClass);
        $criteria = [$constraint->property => $value];

        if (is_array($value)) {
            $count = $repository->count($criteria);
            if ($count === count($value)) {
                return;
            }
        } else {
            $entity = $repository->findOneBy($criteria);
            if ($entity !== null) {
                return;
            }
        }

        $valueString = is_array($value)
            ? $this->formatValues($value)
            : $this->formatValue($value);

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ class }}', $constraint->class)
            ->setParameter('{{ property }}', $constraint->property)
            ->setParameter('{{ value }}', $valueString)
            ->addViolation();
    }
}
