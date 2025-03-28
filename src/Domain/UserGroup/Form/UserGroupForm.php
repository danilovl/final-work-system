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

namespace App\Domain\UserGroup\Form;

use App\Domain\UserGroup\Model\UserGroupModel;
use Override;
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface
};
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserGroupForm extends AbstractType
{
    final public const string NAME = 'user_group';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'required' => true
        ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserGroupModel::class
        ]);
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
