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

namespace App\Service\Conversation;

use App\Model\Conversation\ConversationMessageFacade;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use DateTime;
use Twig\Environment;
use App\Entity\{
    Conversation,
    ConversationMessage
};

class ConversationStreamService
{
    private ?DateTime $date;

    public function __construct(
        private ParameterServiceInterface $parameterService,
        private Environment $twig,
        private ConversationMessageFacade $conversationMessageFacade
    ) {
    }

    private function getLastMessage(Conversation $conversation): ?string
    {
        $this->date = $this->date ?? new DateTime;

        /** @var ConversationMessage[] $messages */
        $messages = $this->conversationMessageFacade->getMessagesByConversationAfterDate($conversation, $this->date);

        $chatMessageHtml = null;
        foreach ($messages as $message) {
            $chatMessageHtml .= $this->twig->render('conversation/include/chat_message.html.twig', [
                'message' => $message
            ]);

            $this->date = $message->getCreatedAt();
        }

        return $chatMessageHtml !== null ? base64_encode($chatMessageHtml) : null;
    }

    public function handle(Conversation $conversation): callable
    {
        $sleepSecond = $this->parameterService->get('event_source.conversation.detail.sleep');

        return function () use ($conversation, $sleepSecond): void {
            while (true) {
                echo 'data: ' . $this->getLastMessage($conversation) . "\n\n";
                ob_flush();
                flush();
                sleep($sleepSecond);
            }
        };
    }
}
