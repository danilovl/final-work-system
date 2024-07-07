<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Domain\ConversationMessage\Form;

use App\Domain\ConversationMessage\Model\ConversationMessageModel;
use App\Domain\User\Entity\User;
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface
};
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConversationMessageForm extends AbstractType
{
    final public const string NAME = 'conversation_message';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $options['user'];

        $builder->add('content', TextareaType::class, [
            'required' => true,
            'data' => $user->getMessageHeaderFooter(),
            'constraints' => [
                new NotBlank
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ConversationMessageModel::class
            ])
            ->setRequired([
                'user'
            ])
            ->setAllowedTypes('user', User::class);
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
