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

namespace App\Infrastructure\Web\Form\Type;

use App\Application\Constant\DateFormatConstant;
use App\Infrastructure\Validation\Constraint\FirstWeekDay;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\{
    Options,
    OptionsResolver
};

final class FirstWeekDayType extends AbstractType
{
    final public const string NAME = 'first_week_day_type';

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
            'format' => DateFormatConstant::WIDGET_SINGLE_TEXT_DATE->value,
            'html5' => false
        ]);

        $this->fixingOptions($resolver);
    }

    private function fixingOptions(OptionsResolver $resolver): void
    {
        $resolver->setNormalizer('constraints', static fn (Options $options, $value): array => array_merge($value, [new FirstWeekDay]));
    }

    #[Override]
    public function getParent(): string
    {
        return DateType::class;
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
