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

use App\Application\Constant\FlashTypeConstant;
use App\Application\Form\Factory\FormDeleteFactory;
use App\Application\Service\{
    RequestService,
    EntityManagerService
};
use App\Domain\EventSchedule\Entity\EventSchedule;
use Symfony\Component\HttpFoundation\{
    RedirectResponse,
    Request
};

class EventScheduleDeleteHandle
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly EntityManagerService $entityManagerService,
        private readonly FormDeleteFactory $formDeleteFactory
    ) {}

    public function handle(Request $request, EventSchedule $eventSchedule): RedirectResponse
    {
        $form = $this->formDeleteFactory
            ->createDeleteForm($eventSchedule, 'event_schedule_delete')
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->entityManagerService->remove($eventSchedule);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.delete.success');

                return $this->requestService->redirectToRoute('event_schedule_list');
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.delete.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.delete.error');
        }

        return $this->requestService->redirectToRoute('event_schedule_list');
    }
}
