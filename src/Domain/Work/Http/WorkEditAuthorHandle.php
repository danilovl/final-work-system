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

namespace App\Domain\Work\Http;

use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Domain\Work\Bus\Command\EditAuthor\EditAuthorCommand;
use App\Application\Constant\SeoPageConstant;
use App\Infrastructure\Service\{
    RequestService,
    SeoPageService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\User\Form\UserEditForm;
use App\Domain\User\Model\UserModel;
use App\Domain\Work\Entity\Work;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class WorkEditAuthorHandle
{
    public function __construct(
        private RequestService $requestService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private HashidsServiceInterface $hashidsService,
        private FormFactoryInterface $formFactory,
        private SeoPageService $seoPageService,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request, Work $work): Response
    {
        $author = $work->getAuthor();
        $userModel = UserModel::fromUser($author);

        $form = $this->formFactory
            ->create(UserEditForm::class, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = EditAuthorCommand::create($author, $userModel, $work);
            $this->commandBus->dispatch($command);

            return $this->requestService->redirectToRoute('work_edit_author', [
                'id' => $this->hashidsService->encode($work->getId())
            ]);
        }

        $this->seoPageService->addTitle($work->getTitle(), SeoPageConstant::DASH_SEPARATOR->value);

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'domain/work/edit_author.html.twig');

        return $this->twigRenderService->renderToResponse($template, [
            'work' => $work,
            'user' => $author,
            'form' => $form->createView(),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.update_and_close')
        ]);
    }
}
