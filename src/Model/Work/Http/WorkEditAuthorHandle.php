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

namespace App\Model\Work\Http;

use App\Model\Work\Entity\Work;
use App\Model\User\Form\UserEditForm;
use App\Model\User\Factory\UserFactory;
use App\Model\User\UserModel;
use App\Model\Work\EventDispatcher\WorkEventDispatcherService;
use App\Constant\{
    SeoPageConstant,
    FlashTypeConstant
};
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\{
    RequestService,
    SeoPageService,
    TranslatorService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class WorkEditAuthorHandle
{
    public function __construct(
        private RequestService $requestService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private HashidsServiceInterface $hashidsService,
        private FormFactoryInterface $formFactory,
        private WorkEventDispatcherService $workEventDispatcherService,
        private UserFactory $userFactory,
        private SeoPageService $seoPageService
    ) {
    }

    public function handle(Request $request, Work $work): Response
    {
        $author = $work->getAuthor();
        $userModel = UserModel::fromUser($author);

        $form = $this->formFactory
            ->create(UserEditForm::class, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->userFactory->flushFromModel($userModel, $author);
                $this->workEventDispatcherService->onWorkEditAuthor($work);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.save.success');

                return $this->requestService->redirectToRoute('work_edit_author', [
                    'id' => $this->hashidsService->encode($work->getId())
                ]);
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.save.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.save.error');
        }

        $this->seoPageService->addTitle($work->getTitle(), SeoPageConstant::DASH_SEPARATOR);

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'work/edit_author.html.twig');

        return $this->twigRenderService->render($template, [
            'work' => $work,
            'user' => $author,
            'form' => $form->createView(),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.update_and_close')
        ]);
    }
}
