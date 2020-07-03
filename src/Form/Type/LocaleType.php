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

final class LocaleType extends AbstractType
{
    public const NAME = 'locale_type';
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
        $shoices = [];
        foreach ($this->locales as $locale) {
            $shoices["app.locales.{$locale}"] = $locale;
        }

        return $shoices;

//        return array_map(function (string $locale){
//            return "app.roles.{$locale}" => $locale;
//        }, $this->locales)
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
