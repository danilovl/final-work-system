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

use App\Domain\Media\MediaModel;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\{
    File,
    Image,
    NotBlank
};

class ProfileMediaForm extends AbstractType
{
    final public const NAME = 'user_profile_image';

    public function __construct(private ParameterServiceInterface $parameterService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('uploadMedia', FileType::class, [
            'required' => true,
            'constraints' => [
                new NotBlank,
                new File($this->parameterService->getArray('constraints.profile.file')),
                new Image($this->parameterService->getArray('constraints.profile.image'))
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MediaModel::class
        ]);
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
