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

namespace App\Domain\Document\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    ChoiceType,
    TextType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentSearchForm extends AbstractType
{
    final public const string NAME = 'document_search';

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

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
