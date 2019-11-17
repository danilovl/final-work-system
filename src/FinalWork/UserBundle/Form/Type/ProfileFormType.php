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

namespace FinalWork\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    TextareaType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\{
    InvalidOptionsException,
    MissingOptionsException,
    ConstraintDefinitionException
};
use FOS\UserBundle\Form\Type\ProfileFormType as BaseProfileFormType;

class ProfileFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws MissingOptionsException
     * @throws InvalidOptionsException
     * @throws ConstraintDefinitionException
     */
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
            ->add('phone', TextType::class, [
                'required' => false
            ])
            ->add('skype', TextType::class, [
                'required' => false
            ])
            ->add('facebookUid', TextType::class, [
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

    /**
     * @return string
     */
    public function getParent(): string
    {
        return BaseProfileFormType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'app_user_profile';
    }

}
