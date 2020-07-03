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

namespace App\Form\Type;

use App\Constant\UserRoleConstant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserRoleType extends AbstractType
{
    public const NAME = 'user_role_type';

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => [
                'app.roles.student' => UserRoleConstant::STUDENT,
                'app.roles.opponent' => UserRoleConstant::OPPONENT,
                'app.roles.consultant' => UserRoleConstant::CONSULTANT
            ]
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
