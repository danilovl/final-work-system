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

namespace App\Service;

use App\Constant\TranslationConstant;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslatorService implements TranslatorInterface
{
    public function __construct(
        private TranslatorInterface $translator,
        private RequestStack $requestStack
    ) {
    }

    public function trans(
        string $id,
        array $parameters = [],
        string $domain = null,
        string $locale = null
    ) {
        if (str_contains($id, TranslationConstant::FLASH_START_KEY)) {
            $domain = TranslationConstant::FLASH_DOMAIN;
            $locale = $locale ?? $this->requestStack->getCurrentRequest()->getLocale();
        }

        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function setLocale(string $locale)
    {
        $this->translator->setLocale($locale);
    }
}
