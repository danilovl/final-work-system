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

namespace App\Domain\Conversation\EventDispatcher;

use App\Application\EventSubscriber\Events;
use App\Domain\Conversation\EventDispatcher\GenericEvent\ConversationMessageGenericEvent;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use Danilovl\AsyncBundle\Service\AsyncService;
use App\Infrastructure\Service\EventDispatcherService;

readonly class ConversationEventDispatcher
{
    public function __construct(
        private EventDispatcherService $eventDispatcher,
        private AsyncService $asyncService
    ) {}

    public function onConversationMessageCreate(ConversationMessage $conversationMessage): void
    {
        $genericEvent = new ConversationMessageGenericEvent($conversationMessage);

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::MESSAGE_CREATE);
        });
    }
}
