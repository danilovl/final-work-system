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

namespace App\Domain\Event\Http;

use App\Application\Constant\FlashTypeConstant;
use App\Application\Form\Factory\FormDeleteFactory;
use App\Application\Service\{
    RequestService,
    EntityManagerService
};
use App\Domain\Event\Entity\Event;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\HttpFoundation\{
    RedirectResponse,
    Request
};

class EventDeleteHandle
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly EntityManagerService $entityManagerService,
        private readonly FormDeleteFactory $formDeleteFactory,
        private readonly HashidsServiceInterface $hashidsService,
    ) {}

    public function handle(Request $request, Event $event): RedirectResponse
    {
        $form = $this->formDeleteFactory
            ->createDeleteForm($event, 'event_schedule_delete')
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->entityManagerService->remove($event);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.delete.success');

                return $this->requestService->redirectToRoute('event_calendar_manage');
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.delete.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.delete.error');

            return $this->requestService->redirectToRoute('event_detail', [
                'id' => $this->hashidsService->encode($event->getId())
            ]);
        }

        return $this->requestService->redirectToRoute('event_calendar_manage');
    }
}
