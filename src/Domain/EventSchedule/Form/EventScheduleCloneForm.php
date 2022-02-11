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

namespace App\Domain\EventSchedule\Form;

use App\Application\Form\Type\FirstWeekDayType;
use App\Domain\EventSchedule\EventScheduleCloneModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventScheduleCloneForm extends AbstractType
{
    final public const NAME = 'event_schedule_clone';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('start', FirstWeekDayType::class, [
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
                'data_class' => EventScheduleCloneModel::class
            ]);
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
