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

use App\Application\Service\TwigRenderService;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Facade\ConversationMessageFacade;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use DateTimeImmutable;

class ConversationStreamService
{
    private ?DateTimeImmutable $date;

    public function __construct(
        private readonly ParameterServiceInterface $parameterService,
        private readonly TwigRenderService $twigRenderService,
        private readonly ConversationMessageFacade $conversationMessageFacade
    ) {}

    public function handle(Conversation $conversation): callable
    {
        $sleepSecond = $this->parameterService->getInt('event_source.conversation.detail.sleep');

        return function () use ($conversation, $sleepSecond): void {
            while (true) {
                echo 'data: ' . $this->getLastMessage($conversation) . "\n\n";
                ob_flush();
                flush();
                sleep($sleepSecond);
            }
        };
    }

    private function getLastMessage(Conversation $conversation): ?string
    {
        $this->date ??= new DateTimeImmutable;

        $messages = $this->conversationMessageFacade->getMessagesByConversationAfterDate(
            $conversation,
            $this->date
        );

        $chatMessageHtml = null;
        foreach ($messages as $message) {
            $chatMessageHtml .= $this->twigRenderService->render('domain/conversation/include/chat_message.html.twig', [
                'message' => $message
            ]);

            $this->date = $message->getCreatedAt();
        }

        return $chatMessageHtml !== null ? base64_encode($chatMessageHtml) : null;
    }
}
