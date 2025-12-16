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

namespace App\Domain\Work\Autocompleter;

use App\Domain\WorkType\Entity\WorkType;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\{
    AsEntityAutocompleteField,
    BaseEntityAutocompleteType
};

#[AsEntityAutocompleteField]
class WorkSearchTypeUxAutocompleter extends AbstractType
{
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => WorkType::class,
            'searchable_fields' => ['name'],
            'choice_label' => 'name',
            'required' => false,
            'multiple' => true
        ]);
    }

    #[Override]
    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
