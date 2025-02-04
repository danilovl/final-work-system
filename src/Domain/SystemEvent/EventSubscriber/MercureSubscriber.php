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

namespace App\Domain\SystemEvent\EventSubscriber;

use App\Application\EventDispatcher\GenericEvent\EntityPostFlushGenericEvent;
use App\Application\EventSubscriber\Events;
use App\Domain\SystemEvent\Entity\SystemEvent;
use App\Domain\Widget\WidgetItem\UnreadSystemEventWidget;
use Danilovl\HashidsBundle\Service\HashidsService;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mercure\{
    HubInterface,
    Update};

readonly class MercureSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private HashidsService $hashidsService,
        private UnreadSystemEventWidget $unreadSystemEventWidget,
        private HubInterface $hub
    ) {}

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            Events::ENTITY_POST_PERSIST_FLUSH => ['onSystemEventCreate', -10]
        ];
    }

    public function onSystemEventCreate(EntityPostFlushGenericEvent $genericEvent): void
    {
        $object = $genericEvent->object;
        if (!$object instanceof SystemEvent) {
            return;
        }

        $systemEventRecipients = $object->getRecipient();

        foreach ($systemEventRecipients as $systemEventRecipient) {
            $user = $systemEventRecipient->getRecipient();

            $update = new Update(
                'unread-system-event-widget/' . $this->hashidsService->encode($user->getId()),
                (string) json_encode([
                    'content' => $this->unreadSystemEventWidget->renderForUser($user)
                ]),
            );

            $this->hub->publish($update);
        }
    }
}
