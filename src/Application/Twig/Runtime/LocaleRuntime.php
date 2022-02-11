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

namespace App\Application\Twig\Runtime;

use Symfony\Component\Intl\Locales;
use Twig\Extension\AbstractExtension;
use Twig\Extension\RuntimeExtensionInterface;

class LocaleRuntime extends AbstractExtension implements RuntimeExtensionInterface
{
    public function __construct(private string $locales)
    {
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
}
