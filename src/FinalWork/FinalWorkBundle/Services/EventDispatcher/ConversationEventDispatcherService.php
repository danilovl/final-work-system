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

namespace FinalWork\FinalWorkBundle\Services\EventDispatcher;

use FinalWork\FinalWorkBundle\Entity\ConversationMessage;
use FinalWork\FinalWorkBundle\EventListener\Events;
use Symfony\Component\EventDispatcher\{
    GenericEvent,
    EventDispatcherInterface
};

class ConversationEventDispatcherService
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * ConversationEventDispatcherService constructor.
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param ConversationMessage $conversationMessage
     */
    public function onConversationMessageCreate(ConversationMessage $conversationMessage): void
    {
        $event = new GenericEvent($conversationMessage);

        $this->eventDispatcher->dispatch(Events::NOTIFICATION_MESSAGE_CREATE, $event);
        $this->eventDispatcher->dispatch(Events::SYSTEM_MESSAGE_CREATE, $event);
    }
}
