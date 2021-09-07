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

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface
};
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\{
    Email,
    NotBlank
};
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkUserForm extends AbstractType
{
    public const NAME = 'work_user';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
            ->add('phone', TextType::class, [
                'required' => false
            ])
            ->add('email', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank,
                    new Email
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
