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

namespace App\Domain\ConversationMessage\Form\Constraint;

use App\Domain\ConversationMessage\Model\ConversationComposeMessageModel;
use Symfony\Component\Validator\{
    Constraint,
    ConstraintValidator};
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ConversationMessageNameValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ConversationMessageName) {
            throw new UnexpectedTypeException($constraint, ConversationMessageName::class);
        }

        $form = $this->context->getRoot();
        /** @var ConversationComposeMessageModel $data */
        $data = $form->getData();
        $conversations = $data->conversation !== null ? iterator_to_array($data->conversation) : [];

        if (empty($value) && count($conversations) > 1) {
            $this->context
                ->buildViolation('This value should not be blank.')
                ->addViolation();
        }
    }
}
