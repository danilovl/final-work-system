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

namespace FinalWork\FinalWorkBundle\Form;

use FinalWork\FinalWorkBundle\Entity\{
    EventType,
    EventAddress,
    EventScheduleTemplate
};
use FinalWork\FinalWorkBundle\Form\Constraint\EventScheduleTemplateTime;
use FinalWork\FinalWorkBundle\Form\Type\WeekDaysType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    TimeType,
    ChoiceType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\{
    InvalidOptionsException,
    MissingOptionsException,
    ConstraintDefinitionException
};

class EventScheduleTemplateForm extends AbstractType
{
    public const NAME = 'event_schedule_template';

    /**
     * {@inheritdoc}
     *
     * @throws MissingOptionsException
     * @throws InvalidOptionsException
     * @throws ConstraintDefinitionException
     */
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
                'choice_label' => static function (EventAddress $address): string {
                    return $address->getName();
                }
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

    /**
     * {@inheritdoc}
     *
     * @throws MissingOptionsException
     * @throws InvalidOptionsException
     * @throws ConstraintDefinitionException
     * @throws AccessException
     */
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

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
