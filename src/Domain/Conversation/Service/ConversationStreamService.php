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

use App\Infrastructure\Service\TwigRenderService;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Facade\ConversationMessageFacade;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use DateTimeImmutable;
use Generator;
use Symfony\Component\HttpFoundation\ServerEvent;

class ConversationStreamService
{
    private DateTimeImmutable $date;

    public function __construct(
        private readonly ParameterServiceInterface $parameterService,
        private readonly TwigRenderService $twigRenderService,
        private readonly ConversationMessageFacade $conversationMessageFacade
    ) {
        $this->date = new DateTimeImmutable;
    }

    public function handle(Conversation $conversation): callable
    {
        /** @var positive-int $sleepSecond */
        $sleepSecond = $this->parameterService->getInt('event_source.conversation.detail.sleep');

        return function () use ($conversation, $sleepSecond): Generator {
            $count = 0;
            while (true) {
                if ($count >= 3_600) {
                    break;
                }

                $message = $this->getLastMessage($conversation);
                if ($message === null) {
                    $count += $sleepSecond;

                    continue;
                }

                yield new ServerEvent($message, type: 'jobs');
                sleep($sleepSecond);
                $count += $sleepSecond;
            }
        };
    }

    private function getLastMessage(Conversation $conversation): ?string
    {
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
