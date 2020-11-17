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

namespace App\EventListener\SystemEvent;

use App\EventDispatcher\GenericEvent\UserGenericEvent;
use App\EventListener\Events;
use App\Constant\SystemEventTypeConstant;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Entity\{
    SystemEvent,
    SystemEventType,
    SystemEventRecipient
};

class UserSystemEventSubscriber extends BaseSystemEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::SYSTEM_USER_EDIT => 'onUserEdit'
        ];
    }

    public function onUserEdit(UserGenericEvent $event): void
    {
        $user = $event->user;
        $owner = $event->owner;

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($owner);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::USER_EDIT)
        );

        $recipientAuthor = new SystemEventRecipient;
        $recipientAuthor->setRecipient($user);
        $systemEvent->addRecipient($recipientAuthor);

        $this->em->persistAndFlush($systemEvent);
    }
}
