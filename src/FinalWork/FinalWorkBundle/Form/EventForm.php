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
    EventParticipant
};
use FinalWork\FinalWorkBundle\Constant\FormConstant;
use FinalWork\FinalWorkBundle\Form\Constraint\EventTime;
use FinalWork\FinalWorkBundle\Model\Event\EventModel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    DateType,
    TextType,
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

class EventForm extends AbstractType
{
    public const NAME = 'event';

    /**
     * {@inheritdoc}
     *
     * @throws MissingOptionsException
     * @throws InvalidOptionsException
     * @throws ConstraintDefinitionException
     */
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
                'placeholder' => FormConstant::PLACEHOLDER,
                'choice_label' => static function (EventAddress $address): string {
                    return (string)$address;
                }
            ])
            ->add('participant', ChoiceType::class, [
                'required' => false,
                'choices' => $participants,
                'placeholder' => FormConstant::PLACEHOLDER,
                'choice_label' => static function (EventParticipant $participant): string {
                    return (string)$participant;
                },
                'preferred_choices' => $participants
            ])
            ->add('start', DateType::class, [
                'required' => true,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd HH:mm',
                'constraints' => [
                    new NotBlank
                ]
            ])
            ->add('end', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd HH:mm',
                'required' => true,
                'html5' => false,
                'constraints' => [
                    new NotBlank
                ]
            ]);
    }

    /**
     * {@inheritdoc}
     * @throws MissingOptionsException
     * @throws InvalidOptionsException
     * @throws ConstraintDefinitionException
     * @throws AccessException
     */
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

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
