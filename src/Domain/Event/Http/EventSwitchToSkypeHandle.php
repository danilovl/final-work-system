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
use App\Application\Service\{
    RequestService,
    EntityManagerService
};
use App\Domain\Event\Entity\Event;
use App\Domain\Event\EventDispatcher\EventEventDispatcherService;
use App\Domain\EventAddress\Facade\EventAddressFacade;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EventSwitchToSkypeHandle
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly EntityManagerService $entityManagerService,
        private readonly EventAddressFacade $eventAddressFacade,
        private readonly HashidsServiceInterface $hashidsService,
        private readonly EventEventDispatcherService $eventEventDispatcherService
    ) {}

    public function handle(Event $event): RedirectResponse
    {
        $eventAddressSkype = $this->eventAddressFacade
            ->getSkypeByOwner($event->getOwner());

        if ($eventAddressSkype !== null) {
            $event->setAddress($eventAddressSkype);

            $this->entityManagerService->flush($event);
            $this->eventEventDispatcherService->onEventSwitchToSkype($event);

            $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.save.success');
        } else {
            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.save.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.save.error');
        }

        return $this->requestService->redirectToRoute('event_detail', [
            'id' => $this->hashidsService->encode($event->getId())
        ]);
    }
}
