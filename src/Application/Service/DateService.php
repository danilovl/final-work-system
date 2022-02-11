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

namespace App\Application\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

class DateService
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function getWeekDaysArray(): array
    {
        return [
            0 => $this->translator->trans('app.calendar.day.mo'),
            1 => $this->translator->trans('app.calendar.day.tu'),
            2 => $this->translator->trans('app.calendar.day.we'),
            3 => $this->translator->trans('app.calendar.day.th'),
            4 => $this->translator->trans('app.calendar.day.fr'),
            5 => $this->translator->trans('app.calendar.day.sa'),
            6 => $this->translator->trans('app.calendar.day.su')
        ];
    }
}
