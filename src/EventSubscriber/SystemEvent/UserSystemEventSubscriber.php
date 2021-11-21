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

namespace App\EventSubscriber\SystemEvent;

use App\EventSubscriber\Events;
use App\Constant\SystemEventTypeConstant;
use App\Model\SystemEvent\Entity\SystemEvent;
use App\Model\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Model\SystemEventType\Entity\SystemEventType;
use App\Model\User\EventDispatcher\GenericEvent\UserGenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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

        $systemEventType = $this->entityManagerService
            ->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::USER_EDIT);

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($owner);
        $systemEvent->setType($systemEventType);

        $recipientAuthor = new SystemEventRecipient;
        $recipientAuthor->setRecipient($user);
        $systemEvent->addRecipient($recipientAuthor);

        $this->entityManagerService->persistAndFlush($systemEvent);
    }
}
