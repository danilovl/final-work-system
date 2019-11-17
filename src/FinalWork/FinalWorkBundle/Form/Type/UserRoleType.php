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

namespace FinalWork\FinalWorkBundle\Form\Type;

use FinalWork\FinalWorkBundle\Constant\UserRoleConstant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserRoleType extends AbstractType
{
    public const NAME = 'user_role_type';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => false,
            'choices' => [
                'finalwork.roles.student' => UserRoleConstant::STUDENT,
                'finalwork.roles.opponent' => UserRoleConstant::OPPONENT,
                'finalwork.roles.consultant' => UserRoleConstant::CONSULTANT
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
