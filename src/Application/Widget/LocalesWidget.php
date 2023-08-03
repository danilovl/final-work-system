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

namespace App\Application\Widget;

use App\Application\Service\TwigRenderService;
use Symfony\Component\Intl\Locales;

class LocalesWidget extends BaseWidget
{
    private array $locales;

    public function __construct(
        private readonly TwigRenderService $twigRenderService,
        string $locales
    ) {
        $this->locales = explode('|', $locales);
    }

    public function getRenderParameters(): array
    {
        $locales = [];
        foreach ($this->locales as $localeCode) {
            $locales[] = [
                'code' => $localeCode,
                'name' => Locales::getName($localeCode, $localeCode)
            ];
        }

        return ['locales' => $locales];
    }

    public function render(): string
    {
        return $this->twigRenderService->render('widget/locales.html.twig', $this->getRenderParameters());
    }
}
