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

namespace App\Infrastructure\Validation\Constraint;

use App\Application\Constant\CompareConstant;
use App\Application\Helper\{
    CompareHelper,
    DateHelper
};
use App\Infrastructure\Service\TranslatorService;
use DateTime;
use Override;
use Symfony\Component\Validator\{
    Constraint,
    ConstraintValidator
};
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FirstWeekDayValidator extends ConstraintValidator
{
    public function __construct(private readonly TranslatorService $translator) {}

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }

        if (!$constraint instanceof FirstWeekDay) {
            throw new UnexpectedTypeException($constraint, FirstWeekDay::class);
        }

        if (!$value instanceof DateTime) {
            throw new UnexpectedTypeException($value, DateTime::class);
        }

        $startWeekTest = DateHelper::actualWeekStartByDate(clone $value);
        if (!CompareHelper::compareDateTime($value, $startWeekTest, CompareConstant::NOT_EQUAL)) {
            return;
        }

        $this->context
            ->buildViolation($this->translator->trans($constraint->message))
            ->setTranslationDomain('messages')
            ->atPath('start')
            ->addViolation();
    }
}
