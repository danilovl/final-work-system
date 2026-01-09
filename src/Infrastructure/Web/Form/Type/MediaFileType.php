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

namespace App\Infrastructure\Web\Form\Type;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\{
    Options,
    OptionsResolver
};
use Symfony\Component\Validator\Constraints\{
    File,
    NotBlank
};

final class MediaFileType extends AbstractType
{
    final public const string NAME = 'media_file_type';

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'mimeTypes' => [],
                'uploadMedia' => false,
                'required' => false
            ])
            ->setRequired([
                'mimeTypes',
                'uploadMedia'
            ])
            ->setAllowedTypes('mimeTypes', 'iterable')
            ->setAllowedTypes('uploadMedia', 'bool');

        $this->fixingOptions($resolver);
    }

    private function fixingOptions(OptionsResolver $resolver): void
    {
        $resolver->setNormalizer('constraints', fn (Options $options, $value): array => array_merge($value, $this->uploadMediaConstraints($options)));
        $resolver->setNormalizer('required', fn (Options $options): bool => $this->uploadMediaRequired($options));
    }

    private function uploadMediaConstraints(Options $options): array
    {
        $constraints = [
            new File(['mimeTypes' => $options['mimeTypes']])
        ];

        if (!empty($options['uploadMedia'])) {
            $constraints[] = new NotBlank;
        }

        return $constraints;
    }

    private function uploadMediaRequired(Options $options): bool
    {
        return $options['uploadMedia'];
    }

    #[Override]
    public function getParent(): string
    {
        return FileType::class;
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
