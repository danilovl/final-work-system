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

namespace App\Application\Form\Type;

use App\Domain\User\Constant\UserRoleConstant;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserRoleType extends AbstractType
{
    final public const string NAME = 'user_role_type';

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => [
                'app.roles.student' => UserRoleConstant::STUDENT->value,
                'app.roles.opponent' => UserRoleConstant::OPPONENT->value,
                'app.roles.consultant' => UserRoleConstant::CONSULTANT->value
            ]
        ]);
    }

    #[Override]
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
