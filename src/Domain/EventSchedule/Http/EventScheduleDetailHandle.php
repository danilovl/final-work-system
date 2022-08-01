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

namespace App\Domain\EventSchedule\Http;

use App\Application\Form\Factory\FormDeleteFactory;
use App\Application\Service\{
    DateService,
    SeoPageService,
    TwigRenderService
};
use App\Domain\EventSchedule\Entity\EventSchedule;
use Symfony\Component\HttpFoundation\Response;

class EventScheduleDetailHandle
{
    public function __construct(
        private readonly TwigRenderService $twigRenderService,
        private readonly DateService $dateService,
        private readonly SeoPageService $seoPageService,
        private readonly FormDeleteFactory $formDeleteFactory
    ) {}

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
