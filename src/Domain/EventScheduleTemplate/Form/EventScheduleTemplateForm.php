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

namespace App\Domain\EventScheduleTemplate\Form;

use App\Application\Form\Type\WeekDaysType;
use App\Domain\EventAddress\Entity\EventAddress;
use App\Domain\EventScheduleTemplate\Entity\EventScheduleTemplate;
use App\Domain\EventScheduleTemplate\Form\Constraint\EventScheduleTemplateTime;
use App\Domain\EventType\Entity\EventType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    ChoiceType,
    TextType,
    TimeType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventScheduleTemplateForm extends AbstractType
{
    final public const string NAME = 'event_schedule_template';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', EntityType::class, [
                'class' => EventType::class,
                'required' => true,
                'constraints' => [
                    new NotBlank
                ]
            ])
            ->add('day', WeekDaysType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank
                ]
            ])
            ->add('name', TextType::class, [
                'required' => false
            ])
            ->add('address', ChoiceType::class, [
                'required' => false,
                'choices' => $options['addresses'],
                'choice_label' => static fn(EventAddress $address): string => $address->getName()
            ])
            ->add('start', TimeType::class, [
                'required' => true,
                'widget' => 'choice',
                'constraints' => [
                    new NotBlank
                ]
            ])
            ->add('end', TimeType::class, [
                'required' => true,
                'widget' => 'choice',
                'constraints' => [
                    new NotBlank
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                    'data_class' => EventScheduleTemplate::class,
                    'constraints' => [
                        new EventScheduleTemplateTime
                    ]
                ]
            )
            ->setRequired([
                'addresses'
            ])
            ->setAllowedTypes('addresses', 'iterable');
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
