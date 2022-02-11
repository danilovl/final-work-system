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

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Service\ConversationStreamService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConversationLiveHandle
{
    public function __construct(private ConversationStreamService $conversationStreamService)
    {
    }

    public function handle(Conversation $conversation): StreamedResponse
    {
        $response = new StreamedResponse(
            $this->conversationStreamService->handle($conversation)
        );
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Cach-Control', 'no-cache');

        return $response;
    }
}
