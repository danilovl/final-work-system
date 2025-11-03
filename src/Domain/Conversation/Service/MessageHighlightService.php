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

namespace App\Domain\Conversation\Service;

use App\Application\Service\EntityManagerService;
use App\Application\Util\TextHighlightWordUtil;
use Danilovl\AsyncBundle\Service\AsyncService;
use App\Domain\ConversationMessage\Entity\ConversationMessage;

readonly class MessageHighlightService
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private AsyncService $asyncService
    ) {}

    public function addHighlight(iterable $conversationMessages, string $search): void
    {
        if (empty($search)) {
            return;
        }

        /** @var string[] $words */
        $words = preg_split('~\s+~', $search);
        /** @var ConversationMessage $conversationMessage */
        foreach ($conversationMessages as $conversationMessage) {
            $message = TextHighlightWordUtil::highlightPartWords($conversationMessage->getContent(), $words);
            $conversationMessage->setContent($message);

            $this->asyncService->add(callable: function () use ($conversationMessage): void {
                $this->entityManagerService->detach($conversationMessage);
            }, priority: 999);
        }
    }
}
