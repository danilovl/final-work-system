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

namespace FinalWork\FinalWorkBundle\Services;

use Symfony\Component\Translation\DataCollectorTranslator;

class DateService
{
    /**
     * @var DataCollectorTranslator
     */
    private $translator;

    /**
     * DateService constructor.
     * @param DataCollectorTranslator $dataCollectorTranslator
     */
    public function __construct(DataCollectorTranslator $dataCollectorTranslator)
    {
        $this->translator = $dataCollectorTranslator;
    }

    /**
     * @return array
     */
    public function getWeekDaysArray(): array
    {
        return [
            0 => $this->translator->trans('finalwork.calendar.day.mo'),
            1 => $this->translator->trans('finalwork.calendar.day.tu'),
            2 => $this->translator->trans('finalwork.calendar.day.we'),
            3 => $this->translator->trans('finalwork.calendar.day.th'),
            4 => $this->translator->trans('finalwork.calendar.day.fr'),
            5 => $this->translator->trans('finalwork.calendar.day.sa'),
            6 => $this->translator->trans('finalwork.calendar.day.su')
        ];
    }
}
