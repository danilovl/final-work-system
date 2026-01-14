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

namespace App\Infrastructure\Web\Twig\Runtime;

use Danilovl\RenderServiceTwigExtensionBundle\Attribute\AsTwigFunction;
use Symfony\Component\Intl\Locales;

class LocaleRuntime
{
    public function __construct(private readonly string $locales) {}

    #[AsTwigFunction('locales')]
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
}
