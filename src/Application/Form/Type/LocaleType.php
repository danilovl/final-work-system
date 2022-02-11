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

namespace App\Application\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class LocaleType extends AbstractType
{
    final public const NAME = 'locale_type';
    private array $locales;

    public function __construct(string $locales)
    {
        $this->locales = explode('|', $locales);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->getChoices()
        ]);
    }

    private function getChoices(): array
    {
        $choices = [];
        foreach ($this->locales as $locale) {
            $choices["app.locales.{$locale}"] = $locale;
        }

        return $choices;
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
