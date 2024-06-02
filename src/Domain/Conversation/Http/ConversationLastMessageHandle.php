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

namespace App\Domain\Conversation\Http;

use App\Application\Form\SimpleSearchForm;
use App\Application\Model\SearchModel;
use App\Application\Service\{
    SeoPageService,
    TwigRenderService
};
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Facade\ConversationMessageFacade;
use App\Domain\Conversation\Service\MessageHighlightService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\Form\FormFactoryInterface;

readonly class ConversationLastMessageHandle
{
    public function __construct(
        private TwigRenderService $twigRenderService,
        private ConversationMessageFacade $conversationMessageFacade,
        private SeoPageService $seoPageService,
        private ParameterServiceInterface $parameterService,
        private FormFactoryInterface $formFactory,
        private MessageHighlightService $messageHighlightService
    ) {}

    public function handle(Request $request, Conversation $conversation): Response
    {
        $conversationMessages = $this->conversationMessageFacade->getMessagesByConversation(
            $conversation,
            $this->parameterService->getInt('pagination.conversation.message_list')
        );

        $searchModel = new SearchModel;
        $searchForm = $this->formFactory
            ->create(SimpleSearchForm::class, $searchModel)
            ->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $this->messageHighlightService->addHighlight($conversationMessages, $searchModel);
        }

        $this->seoPageService->setTitle($conversation->getTitle());
        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'domain/conversation/last_message.html.twig');

        return $this->twigRenderService->renderToResponse($template, [
            'conversation' => $conversation,
            'conversationMessages' => $conversationMessages
        ]);
    }
}
