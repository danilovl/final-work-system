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

namespace App\Twig;

use Symfony\Component\Intl\Locales;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ChangeLanguageExtension extends AbstractExtension
{
    private string $locales;

    public function __construct(string $locales)
    {
        $this->locales = $locales;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('locales', [$this, 'getLocales']),
        ];
    }

    public function getLocales(): array
    {
        $localeCodes = explode('|', $this->locales);

        $locales = [];
        foreach ($localeCodes as $localeCode) {
            $locales[] = [
                'code' => $localeCode,
                'name' => Locales::getName($localeCode, $localeCode)
            ];
        }

        return $locales;
    }

    public function getName(): string
    {
        return 'app.extension';
    }
}
