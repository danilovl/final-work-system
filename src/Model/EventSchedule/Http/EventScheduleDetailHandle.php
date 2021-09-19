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

namespace App\Model\EventSchedule\Http;

use App\Entity\EventSchedule;
use App\Form\Factory\FormDeleteFactory;
use App\Service\{
    DateService,
    SeoPageService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\Response;

class EventScheduleDetailHandle
{
    public function __construct(
        private TwigRenderService $twigRenderService,
        private DateService $dateService,
        private SeoPageService $seoPageService,
        private FormDeleteFactory $formDeleteFactory
    ) {
    }

    public function handle(EventSchedule $eventSchedule): Response
    {
        $this->seoPageService->setTitle($eventSchedule->getName());

        $deleteForm = $this->formDeleteFactory
            ->createDeleteForm($eventSchedule, 'event_schedule_delete')
            ->createView();

        return $this->twigRenderService->render('event_schedule/detail.html.twig', [
            'eventSchedule' => $eventSchedule,
            'weekDay' => $this->dateService->getWeekDaysArray(),
            'deleteForm' => $deleteForm
        ]);
    }
}
