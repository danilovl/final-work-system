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

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class LocaleType extends AbstractType
{
    final public const string NAME = 'locale_type';

    /**
     * @var string[]
     */
    private array $locales;

    public function __construct(string $locales)
    {
        $this->locales = explode('|', $locales);
    }

    #[Override]
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

    #[Override]
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
