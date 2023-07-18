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

namespace App\Domain\Event\Form;

use App\Application\Constant\{
    DateFormatConstant,
    FormConstant};
use App\Domain\Event\Form\Constraint\EventTime;
use App\Domain\Event\Model\EventModel;
use App\Domain\EventAddress\Entity\EventAddress;
use App\Domain\EventParticipant\Entity\EventParticipant;
use App\Domain\EventType\Entity\EventType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    ChoiceType,
    DateType,
    TextType};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventForm extends AbstractType
{
    final public const NAME = 'event';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $participants = $options['participants'];

        $builder
            ->add('type', EntityType::class, [
                'class' => EventType::class,
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
                'placeholder' => FormConstant::PLACEHOLDER->value,
                'choice_label' => static fn(EventAddress $address): string => (string) $address
            ])
            ->add('participant', ChoiceType::class, [
                'required' => false,
                'choices' => $participants,
                'placeholder' => FormConstant::PLACEHOLDER->value,
                'choice_label' => static fn(EventParticipant $participant): string => (string) $participant,
                'preferred_choices' => $participants
            ])
            ->add('start', DateType::class, [
                'required' => true,
                'widget' => 'single_text',
                'format' => DateFormatConstant::WIDGET_SINGLE_TEXT_DATE_TIME->value,
                'html5' => false,
                'constraints' => [
                    new NotBlank
                ]
            ])
            ->add('end', DateType::class, [
                'widget' => 'single_text',
                'format' => DateFormatConstant::WIDGET_SINGLE_TEXT_DATE_TIME->value,
                'required' => true,
                'html5' => false,
                'constraints' => [
                    new NotBlank
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                    'data_class' => EventModel::class,
                    'constraints' => [
                        new EventTime
                    ],
                ]
            )
            ->setRequired([
                'addresses',
                'participants'
            ])
            ->setAllowedTypes('addresses', 'iterable')
            ->setAllowedTypes('participants', 'iterable');
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
