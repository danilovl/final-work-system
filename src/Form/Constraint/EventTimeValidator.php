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

use App\Helper\CompareHelper;
use App\Constant\{
    CompareConstant,
    EventTypeConstant,
    DateFormatConstant
};
use App\Model\Event\EventModel;
use Symfony\Component\Validator\{
    Constraint,
    ConstraintValidator
};
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EventTimeValidator extends ConstraintValidator
{
    public function validate($eventModel, Constraint $constraint): void
    {
        if ($eventModel === null) {
            return;
        }

        if (!$eventModel instanceof EventModel) {
            throw new UnexpectedTypeException($eventModel, EventModel::class);
        }

        switch ($eventModel->type->getId()) {
            case EventTypeConstant::CONSULTATION:
                if ($eventModel->address === null) {
                    $this->context
                        ->buildViolation('This value should not be blank.')
                        ->atPath('address')
                        ->addViolation();
                }
                break;
            case EventTypeConstant::PERSONAL:
                if ($eventModel->name === null) {
                    $this->context
                        ->buildViolation('This value should not be blank.')
                        ->atPath('name')
                        ->addViolation();
                }
        }

        if (CompareHelper::compareDateTime($eventModel->start, $eventModel->end, CompareConstant::EQUAL)) {
            $this->context
                ->buildViolation('This value should not be equal to {{ compared_value }}.')
                ->setParameter('{{ compared_value }}', $eventModel->start->format(DateFormatConstant::DATE_TIME))
                ->atPath('end')
                ->addViolation();
        }

        if (CompareHelper::compareDateTime($eventModel->start, $eventModel->end, CompareConstant::MORE)) {
            $this->context
                ->buildViolation('This value should be greater than {{ compared_value }}.')
                ->setParameter('{{ compared_value }}', $eventModel->start->format(DateFormatConstant::DATE_TIME))
                ->atPath('start')
                ->addViolation();
        }
    }
}
