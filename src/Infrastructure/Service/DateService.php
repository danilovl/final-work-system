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

namespace App\Infrastructure\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

readonly class DateService
{
    public function __construct(private TranslatorInterface $translator) {}

    public function getWeekDaysArray(?string $locale = null): array
    {
        return [
            0 => $this->translator->trans('app.calendar.day.mo', locale: $locale),
            1 => $this->translator->trans('app.calendar.day.tu', locale: $locale),
            2 => $this->translator->trans('app.calendar.day.we', locale: $locale),
            3 => $this->translator->trans('app.calendar.day.th', locale: $locale),
            4 => $this->translator->trans('app.calendar.day.fr', locale: $locale),
            5 => $this->translator->trans('app.calendar.day.sa', locale: $locale),
            6 => $this->translator->trans('app.calendar.day.su', locale: $locale)
        ];
    }
}
