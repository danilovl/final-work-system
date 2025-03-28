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

namespace App\Domain\Document\Form;

use App\Domain\MediaCategory\Entity\MediaCategory;
use App\Domain\MediaMimeType\Entity\MediaMimeType;
use Override;
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface
};
use Symfony\Component\Form\Extension\Core\Type\{
    ChoiceType,
    TextType
};
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentSearchForm extends AbstractType
{
    final public const string NAME = 'document_search';

    /**
     * @param array{categories: MediaCategory[], mimeType: MediaMimeType[]} $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false
            ])
            ->add('categories', ChoiceType::class, [
                'choice_label' => 'name',
                'required' => false,
                'multiple' => true,
                'choices' => $options['categories']
            ])
            ->add('mimeType', ChoiceType::class, [
                'choice_label' => 'extension',
                'multiple' => true,
                'required' => false,
                'choices' => $options['mimeType']
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'categories' => [],
                'mimeType' => []
            ])
            ->setAllowedTypes('categories', 'iterable')
            ->setAllowedTypes('mimeType', 'iterable');
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
