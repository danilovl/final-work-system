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

namespace App\Application\Service;

use App\Application\Constant\TranslationConstant;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslatorService implements TranslatorInterface
{
    private TranslatorInterface|Translator $translator;

    public function __construct(
        TranslatorInterface $translator,
        private readonly RequestStack $requestStack,
        private readonly string $locale,
    ) {
        $this->translator = $translator;
    }

    public function trans(
        string $id,
        array $parameters = [],
        string $domain = null,
        string $locale = null
    ): string {
        if (str_contains($id, TranslationConstant::FLASH_START_KEY->value)) {
            $domain = TranslationConstant::FLASH_DOMAIN->value;
            $locale = $locale ?? $this->requestStack->getCurrentRequest()?->getLocale();
        }

        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function setLocale(string $locale): void
    {
        $this->translator->setLocale($locale);
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
