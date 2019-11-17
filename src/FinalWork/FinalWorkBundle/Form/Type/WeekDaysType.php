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

namespace FinalWork\FinalWorkBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class WeekDaysType extends AbstractType
{
    public const NAME = 'week_days_type';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => [
                'finalwork.calendar.day.mo' => 0,
                'finalwork.calendar.day.tu' => 1,
                'finalwork.calendar.day.we' => 2,
                'finalwork.calendar.day.th' => 3,
                'finalwork.calendar.day.fr' => 4,
                'finalwork.calendar.day.sa' => 5,
                'finalwork.calendar.day.su' => 6
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
