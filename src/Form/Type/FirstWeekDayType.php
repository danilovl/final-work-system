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

use App\Constant\DateFormatConstant;
use App\Form\Constraint\FirstWeekDay;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\{
    Options,
    OptionsResolver
};

final class FirstWeekDayType extends AbstractType
{
    public const NAME = 'first_week_day_type';

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
            'format' => DateFormatConstant::WIDGET_SINGLE_TEXT_DATE,
            'html5' => false
        ]);

        $this->fixingOptions($resolver);
    }

    private function fixingOptions(OptionsResolver $resolver): void
    {
        $resolver->setNormalizer('constraints', static function (Options $options, $value): array {
            return array_merge($value, [
                new FirstWeekDay
            ]);
        });
    }

    public function getParent(): string
    {
        return DateType::class;
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
