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

namespace App\Form\Constraint;

use App\Constant\DateFormatConstant;
use DateTime;
use App\Helper\DateHelper;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\{
    Constraint,
    ConstraintValidator
};
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FirstWeekDayValidator extends ConstraintValidator
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function validate($startDate, Constraint $constraint): void
    {
        if ($startDate === null) {
            return;
        }

        if (!$startDate instanceof DateTime) {
            throw new UnexpectedTypeException($startDate, DateTime::class);
        }

        $startWeekTest = DateHelper::actualWeekStartByDate(clone $startDate);
        if ($startDate->format(DateFormatConstant::DATE) !== $startWeekTest->format(DateFormatConstant::DATE)) {
            $this->context
                ->buildViolation($this->translator->trans($constraint->message))
                ->setTranslationDomain('messages')
                ->atPath('start')
                ->addViolation();
        }
    }
}