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

namespace App\Domain\EventScheduleTemplate\Form\Constraint;

use App\Application\Constant\{
    EventTypeConstant
};
use App\Application\Constant\CompareConstant;
use App\Application\Helper\CompareHelper;
use App\Domain\EventScheduleTemplate\Entity\EventScheduleTemplate;
use Symfony\Component\Validator\{
    Constraint,
    ConstraintValidator
};
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EventScheduleTemplateTimeValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }

        if (!$constraint instanceof EventScheduleTemplateTime) {
            throw new UnexpectedTypeException($constraint, EventScheduleTemplateTime::class);
        }

        if (!$value instanceof EventScheduleTemplate) {
            throw new UnexpectedTypeException($value, EventScheduleTemplate::class);
        }

        if (CompareHelper::compareDateTime($value->getStart(), $value->getEnd(), CompareConstant::MORE)) {
            $this->context
                ->buildViolation('app.form.validation.date_start_more_end')
                ->setTranslationDomain('messages')
                ->atPath('start')
                ->addViolation();
        }

        switch ($value->getType()->getId()) {
            case  EventTypeConstant::CONSULTATION:
                if ($value->getAddress() === null) {
                    $this->context
                        ->buildViolation('This value should not be blank.')
                        ->atPath('address')
                        ->addViolation();
                }
                break;
            case EventTypeConstant::PERSONAL:
                if ($value->getName() === null) {
                    $this->context
                        ->buildViolation('This value should not be blank.')
                        ->atPath('name')
                        ->addViolation();
                }
        }

        if (CompareHelper::compareDateTime($value->getStart(), $value->getEnd(), CompareConstant::EQUAL)) {
            $this->context
                ->buildViolation('This value should not be equal to {{ compared_value }}.')
                ->setParameter('{{ compared_value }}', $value->getStart()->format('H:i'))
                ->atPath('end')
                ->addViolation();
        }

        if (CompareHelper::compareDateTime($value->getStart(), $value->getEnd(), CompareConstant::MORE)) {
            $this->context
                ->buildViolation('This value should be greater than {{ compared_value }}.')
                ->setParameter('{{ compared_value }}', $value->getStart()->format('H:i'))
                ->atPath('start')
                ->addViolation();
        }
    }
}