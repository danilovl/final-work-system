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

namespace App\EventDispatcher;

use App\Entity\ConversationMessage;
use App\EventDispatcher\GenericEvent\ConversationMessageGenericEvent;
use App\EventListener\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConversationEventDispatcherService
{
    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
    }

    public function onConversationMessageCreate(ConversationMessage $conversationMessage): void
    {
        $genericEvent = new ConversationMessageGenericEvent;
        $genericEvent->conversationMessage = $conversationMessage;

        $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_MESSAGE_CREATE);
        $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_MESSAGE_CREATE);
    }
}
