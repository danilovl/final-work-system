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
use App\Constant\FlashTypeConstant;
use App\Service\{
    RequestService,
    EntityManagerService
};
use Symfony\Component\HttpFoundation\{
    Request,
    RedirectResponse
};

class EventScheduleDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private EntityManagerService $entityManagerService,
        private FormDeleteFactory $formDeleteFactory
    ) {
    }

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
