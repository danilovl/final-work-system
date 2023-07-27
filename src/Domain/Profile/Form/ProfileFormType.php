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

namespace App\Domain\Profile\Form;

use App\Application\Form\Type\LocaleType;
use App\Domain\User\Model\UserModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    CheckboxType,
    TextareaType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileFormType extends AbstractType
{
    final public const NAME = 'app_user_profile';

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
                ]
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
                ]
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank
                ]
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserModel::class
        ]);
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
