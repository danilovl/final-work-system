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

namespace FinalWork\FinalWorkBundle\Form\Constraint;

use Symfony\Component\Validator\{
    Constraint,
    ConstraintValidator
};
use FinalWork\FinalWorkBundle\Model\ConversationMessage\ConversationComposeMessageModel;

class ConversationMessageNameValidator extends ConstraintValidator
{
    /**
     * @param $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        $form = $this->context->getRoot();
        /** @var ConversationComposeMessageModel $data */
        $data = $form->getData();

        if (empty($value) && count($data->conversation) > 1) {
            $this->context
                ->buildViolation('This value should not be blank.')
                ->addViolation();
        }
    }
}