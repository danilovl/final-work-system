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

namespace App\Domain\Event\Form\Constraint;

use App\Application\Constant\{
    CompareConstant,
    DateFormatConstant
};
use App\Application\Helper\CompareHelper;
use App\Domain\Event\Model\EventModel;
use App\Domain\EventType\Constant\EventTypeConstant;
use Symfony\Component\Validator\{
    Constraint,
    ConstraintValidator
};
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EventTimeValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }

        if (!$constraint instanceof EventTime) {
            throw new UnexpectedTypeException($constraint, EventTime::class);
        }

        if (!$value instanceof EventModel) {
            throw new UnexpectedTypeException($value, EventModel::class);
        }

        switch ($value->type->getId()) {
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

        if (CompareHelper::compareDateTime($value->start, $value->end, CompareConstant::EQUAL)) {
            $this->context
                ->buildViolation('This value should not be equal to {{ compared_value }}.')
                ->setParameter('{{ compared_value }}', $value->start->format(DateFormatConstant::DATE_TIME->value))
                ->atPath('end')
                ->addViolation();
        }

        if (CompareHelper::compareDateTime($value->start, $value->end, CompareConstant::MORE)) {
            $this->context
                ->buildViolation('This value should be greater than {{ compared_value }}.')
                ->setParameter('{{ compared_value }}', $value->start->format(DateFormatConstant::DATE_TIME->value))
                ->atPath('start')
                ->addViolation();
        }
    }
}
