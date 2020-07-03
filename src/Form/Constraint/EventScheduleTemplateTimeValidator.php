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

use App\Constant\EventTypeConstant;
use App\Entity\EventScheduleTemplate;
use Symfony\Component\Validator\{
    Constraint,
    ConstraintValidator
};
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EventScheduleTemplateTimeValidator extends ConstraintValidator
{
    public function validate($data, Constraint $constraint): void
    {
        if ($data === null) {
            return;
        }

        if (!$data instanceof EventScheduleTemplate) {
            throw new UnexpectedTypeException($data, EventScheduleTemplate::class);
        }

        if ($data->getStart() > $data->getEnd()) {
            $this->context
                ->buildViolation('app.form.validation.date_start_more_end')
                ->setTranslationDomain('messages')
                ->atPath('start')
                ->addViolation();
        }

        switch ($data->getType()->getId()) {
            case  EventTypeConstant::CONSULTATION:
                if ($data->getAddress() === null) {
                    $this->context
                        ->buildViolation('This value should not be blank.')
                        ->atPath('address')
                        ->addViolation();
                }
                break;
            case EventTypeConstant::PERSONAL:
                if ($data->getName() === null) {
                    $this->context
                        ->buildViolation('This value should not be blank.')
                        ->atPath('name')
                        ->addViolation();
                }
        }

        if ($data->getStart() == $data->getEnd()) {
            $this->context
                ->buildViolation('This value should not be equal to {{ compared_value }}.')
                ->setParameter('{{ compared_value }}', $data->getStart()->format('H:i'))
                ->atPath('end')
                ->addViolation();
        }

        if ($data->getStart() > $data->getEnd()) {
            $this->context
                ->buildViolation('This value should be greater than {{ compared_value }}.')
                ->setParameter('{{ compared_value }}', $data->getStart()->format('H:i'))
                ->atPath('start')
                ->addViolation();
        }
    }
}