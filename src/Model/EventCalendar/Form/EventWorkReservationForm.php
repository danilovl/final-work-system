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

namespace App\Model\EventCalendar\Form;

use App\Model\EventWorkReservation\EventWorkReservationModel;
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface
};
use App\Model\Work\Entity\Work;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventWorkReservationForm extends AbstractType
{
    public const NAME = 'event_participant';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('work', ChoiceType::class, [
            'choices' => $options['works'],
            'choice_label' => static fn(Work $work): string => $work->getTitle(),
            'required' => true,
            'constraints' => [
                new NotBlank
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => EventWorkReservationModel::class,
                'works' => []
            ])
            ->setRequired([
                'works'
            ])
            ->setAllowedTypes('works', 'iterable');
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
