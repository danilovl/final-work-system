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

namespace App\Domain\Conversation\Http\Ajax;

use App\Application\Util\StreamedResponseUtil;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Service\ConversationStreamService;
use Symfony\Component\HttpFoundation\StreamedResponse;

readonly class ConversationLiveHandle
{
    public function __construct(private ConversationStreamService $conversationStreamService) {}

    public function handle(Conversation $conversation): StreamedResponse
    {
        $callback = $this->conversationStreamService->handle($conversation);

        return StreamedResponseUtil::create($callback);
    }
}
