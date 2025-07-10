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

namespace App\Domain\Work\Form;

use App\Domain\Work\Autocompleter\{
    WorkSearchTypeUxAutocompleter,
    WorkStatusTypeUxAutocompleter
};
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\WorkSearch\Model\WorkSearchModel;
use Danilovl\SelectAutocompleterBundle\Form\Type\MultipleAutocompleterType;
use Override;
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface
};
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkSearchForm extends AbstractType
{
    final public const string NAME = 'work_search';

    /**
     * @param array{type: string} $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $type = $options['type'];

        $builder
            ->add('title', TextType::class, [
                'required' => false
            ])
            ->add('shortcut', TextType::class, [
                'required' => false
            ])
            ->add('status', WorkStatusTypeUxAutocompleter::class)
            ->add('type', WorkSearchTypeUxAutocompleter::class);

        $formTypes = ['author', 'supervisor', 'opponent', 'consultant'];
        foreach ($formTypes as $formType) {
            if ($formType === $type) {
                continue;
            }

            $autocompleter = sprintf('own.work-search-%s', $formType);

            $builder->add($formType, MultipleAutocompleterType::class, [
                'autocompleter' => [
                    'name' => $autocompleter,
                    'select_option' => [
                        'multiple' => true
                    ],
                    'route' => [
                        'extra' => ['type' => $type]
                    ]
                ],
                'required' => false
            ]);
        }

        if ($type === WorkUserTypeConstant::SUPERVISOR->value) {
            $builder->add('deadline', MultipleAutocompleterType::class, [
                'autocompleter' => [
                    'name' => 'own.work-search-deadline',
                    'select_option' => [
                        'multiple' => true
                    ]
                ],
                'required' => false
            ]);
        }
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => WorkSearchModel::class
            ])
            ->setRequired('type')
            ->setAllowedTypes('type', 'string');
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
