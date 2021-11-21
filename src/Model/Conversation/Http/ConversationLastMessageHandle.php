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

namespace App\Model\Conversation\Http;

use App\Model\Conversation\Entity\Conversation;
use App\Model\Conversation\Facade\ConversationMessageFacade;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use App\Service\{
    SeoPageService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class ConversationLastMessageHandle
{
    public function __construct(
        private TwigRenderService $twigRenderService,
        private ConversationMessageFacade $conversationMessageFacade,
        private SeoPageService $seoPageService,
        private ParameterServiceInterface $parameterService
    ) {
    }

    public function handle(Request $request, Conversation $conversation): Response
    {
        $conversationMessages = $this->conversationMessageFacade->getMessagesByConversation(
            $conversation,
            $this->parameterService->get('pagination.conversation.message_list')
        );

        $this->seoPageService->setTitle($conversation->getTitle());

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'conversation/last_message.html.twig');

        return $this->twigRenderService->render($template, [
            'conversation' => $conversation,
            'conversationMessages' => $conversationMessages
        ]);
    }
}
