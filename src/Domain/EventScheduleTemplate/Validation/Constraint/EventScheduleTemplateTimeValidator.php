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

namespace App\Domain\EventScheduleTemplate\Validation\Constraint;

use App\Application\Constant\CompareConstant;
use App\Application\Helper\CompareHelper;
use App\Domain\EventScheduleTemplate\Model\EventScheduleTemplateModel;
use App\Domain\EventType\Constant\EventTypeConstant;
use Symfony\Component\Validator\{
    Constraint,
    ConstraintValidator
};
use Override;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EventScheduleTemplateTimeValidator extends ConstraintValidator
{
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }

        if (!$constraint instanceof EventScheduleTemplateTime) {
            throw new UnexpectedTypeException($constraint, EventScheduleTemplateTime::class);
        }

        if (!$value instanceof EventScheduleTemplateModel) {
            throw new UnexpectedTypeException($value, EventScheduleTemplateModel::class);
        }

        $start = $value->start;
        $end = $value->end;
        $type = $value->type;
        if ($start === null || $end === null || $type === null) {
            return;
        }

        if (CompareHelper::compareDateTime($start, $end, CompareConstant::MORE)) {
            $this->context
                ->buildViolation('app.form.validation.date_start_more_end')
                ->setTranslationDomain('messages')
                ->atPath('start')
                ->addViolation();
        }

        switch ($type->getId()) {
            case EventTypeConstant::CONSULTATION->value:
                if ($value->address === null) {
                    $this->context
                        ->buildViolation('This value should not be blank.')
                        ->atPath('address')
                        ->addViolation();
                }

                break;
            case EventTypeConstant::PERSONAL->value:
                if ($value->name === null) {
                    $this->context
                        ->buildViolation('This value should not be blank.')
                        ->atPath('name')
                        ->addViolation();
                }
        }

        if (CompareHelper::compareDateTime($start, $end, CompareConstant::EQUAL)) {
            $this->context
                ->buildViolation('This value should not be equal to {{ compared_value }}.')
                ->setParameter('{{ compared_value }}', $start->format('H:i'))
                ->atPath('end')
                ->addViolation();
        }

        if (CompareHelper::compareDateTime($start, $end, CompareConstant::MORE)) {
            $this->context
                ->buildViolation('This value should be greater than {{ compared_value }}.')
                ->setParameter('{{ compared_value }}', $start->format('H:i'))
                ->atPath('start')
                ->addViolation();
        }
    }
}
