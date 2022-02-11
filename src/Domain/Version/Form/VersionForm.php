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

namespace App\Domain\Version\Form;

use App\Application\Form\Type\MediaFileType;
use App\Domain\Media\MediaModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    TextareaType,
    TextType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class VersionForm extends AbstractType
{
    final public const NAME = 'version';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add('uploadMedia', MediaFileType::class, [
                'mimeTypes' => $options['mimeTypes'],
                'uploadMedia' => $options['uploadMedia']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => MediaModel::class,
                'uploadMedia' => false
            ])
            ->setRequired([
                'mimeTypes'
            ])
            ->setAllowedTypes('uploadMedia', 'bool')
            ->setAllowedTypes('mimeTypes', 'iterable');
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
