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

namespace App\Services;

use App\Model\Conversation\ConversationMessageFacade;
use DateTime;
use Twig\Environment;
use App\Entity\{
    Conversation,
    ConversationMessage
};

class ConversationStreamService
{
    private Environment $twig;
    private ConversationMessageFacade $conversationMessageFacade;
    private ?DateTime $date;

    public function __construct(
        Environment $twig,
        ConversationMessageFacade $conversationMessageFacade
    ) {
        $this->twig = $twig;
        $this->conversationMessageFacade = $conversationMessageFacade;
    }

    public function getLastMessage(Conversation $conversation): string
    {
        $this->date = $this->date ?? new DateTime;

        /** @var ConversationMessage[] $messages */
        $messages = $this->conversationMessageFacade->getMessagesByConversationAfterDate($conversation, $this->date);

        $chatMessageHtml = '';
        foreach ($messages as $message) {
            $chatMessageHtml .= $this->twig->render('conversation/include/chat_message.html.twig', [
                'message' => $message
            ]);

            $this->date = $message->getCreatedAt();
        }

        return base64_encode($chatMessageHtml);
    }

    public function handle(Conversation $conversation): callable
    {
        return function () use ($conversation): void {
            while (true) {
                echo 'data: ' . $this->getLastMessage($conversation) . "\n\n";
                ob_flush();
                flush();
                sleep(3);
            }
        };
    }
}
