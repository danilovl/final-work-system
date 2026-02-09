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

namespace App\Domain\Profile\Form;

use App\Domain\User\Model\UserModel;
use App\Infrastructure\Web\Form\Type\LocaleType;
use Override;
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface
};
use Symfony\Component\Form\Extension\Core\Type\{
    CheckboxType,
    TextareaType,
    TextType
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\{
    Email,
    NotBlank
};

class ProfileFormType extends AbstractType
{
    final public const string NAME = 'app_user_profile';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'required' => false,
                'disabled' => true,
                'constraints' => [
                    new NotBlank
                ]
            ])
            ->add('email', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank,
                    new Email
                ],
                'empty_data' => ''
            ])
            ->add('phone', TextType::class, [
                'required' => false
            ])
            ->add('skype', TextType::class, [
                'required' => false
            ])
            ->add('locale', LocaleType::class, [
                'required' => false
            ])
            ->add('enabledEmailNotification', CheckboxType::class, [
                'required' => false
            ])
            ->add('degreeBefore', TextType::class, [
                'required' => false
            ])
            ->add('firstName', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank
                ],
                'empty_data' => ''
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank
                ],
                'empty_data' => ''
            ])
            ->add('degreeAfter', TextType::class, [
                'required' => false
            ])
            ->add('messageGreeting', TextareaType::class, [
                'required' => false,
            ])
            ->add('messageSignature', TextareaType::class, [
                'required' => false,
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserModel::class
        ]);
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
