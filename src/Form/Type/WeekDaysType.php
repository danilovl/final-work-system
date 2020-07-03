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

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class WeekDaysType extends AbstractType
{
    public const NAME = 'week_days_type';

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => [
                'app.calendar.day.mo' => 0,
                'app.calendar.day.tu' => 1,
                'app.calendar.day.we' => 2,
                'app.calendar.day.th' => 3,
                'app.calendar.day.fr' => 4,
                'app.calendar.day.sa' => 5,
                'app.calendar.day.su' => 6
            ]
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
