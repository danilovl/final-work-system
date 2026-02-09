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

namespace App\Domain\EventSchedule\Form;

use App\Domain\EventAddress\Entity\EventAddress;
use App\Domain\EventSchedule\Model\EventScheduleModel;
use App\Domain\EventScheduleTemplate\Form\EventScheduleTemplateForm;
use Override;
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface
};
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    TextareaType,
    CollectionType
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventScheduleForm extends AbstractType
{
    final public const string NAME = 'event_schedule';

    /**
     * @param array{addresses: EventAddress[]} $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank
                ],
                'empty_data' => ''
            ])
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add('templates', CollectionType::class, [
                'entry_type' => EventScheduleTemplateForm::class,
                'entry_options' => [
                    'addresses' => $options['addresses']
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'attr' => [
                    'class' => 'collection'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank
                ]
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => EventScheduleModel::class
            ])
            ->setRequired([
                'addresses'
            ])
            ->setAllowedTypes('addresses', 'iterable');
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
