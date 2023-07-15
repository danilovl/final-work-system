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

namespace App\Domain\User\Http;

use App\Application\Service\{
    UserService,
    SeoPageService,
    PaginatorService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\User\Facade\UserFacade;
use App\Domain\User\Helper\UserHelper;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\DataTransferObject\WorkRepositoryData;
use App\Domain\Work\Facade\WorkFacade;
use App\Domain\Work\Form\WorkSearchStatusForm;
use App\Domain\WorkStatus\DataTransferObject\WorkStatusRepositoryData;
use App\Domain\WorkStatus\Facade\WorkStatusFacade;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class UserListHandle
{
    public function __construct(
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private UserService $userService,
        private UserFacade $userFacade,
        private FormFactoryInterface $formFactory,
        private PaginatorService $paginatorService,
        private WorkFacade $workFacade,
        private WorkStatusFacade $workStatusFacade,
        private SeoPageService $seoPageService
    ) {}

    public function handle(Request $request): Response
    {
        $user = $this->userService->getUser();

        $type = $request->attributes->get('type');
        $openSearchTab = false;
        $showSearchTab = true;
        $workStatus = null;

        $usersQuery = $this->userFacade->getUsersQueryBySupervisor($user, $type);

        $form = $this->formFactory
            ->create(WorkSearchStatusForm::class)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            $openSearchTab = true;

            if ($form->isValid()) {
                $workStatus = $form->get('status')->getData();
                $usersQuery = $this->userFacade->getUsersQueryBySupervisor($user, $type, $workStatus);
            }
        }

        $getUserWorkAndStatus = true;
        switch ($type) {
            case WorkUserTypeConstant::AUTHOR->value:
                $title = $this->translatorService->trans('app.text.author_list');

                break;
            case WorkUserTypeConstant::OPPONENT->value:
                $title = $this->translatorService->trans('app.text.opponent_list');

                break;
            case WorkUserTypeConstant::CONSULTANT->value:
                $title = $this->translatorService->trans('app.text.consultant_list');

                break;
            default:
                $showSearchTab = false;
                $getUserWorkAndStatus = false;
                $usersQuery = $this->userFacade->queryUnusedUsers($user);
                $title = $this->translatorService->trans('app.text.unused_user_list');

                break;
        }

        $pagination = $this->paginatorService->createPaginationRequest($request, $usersQuery);
        $works = new ArrayCollection;
        $userStatusWorkCounts = new ArrayCollection;

        if ($getUserWorkAndStatus === true) {
            foreach ($pagination as $paginationUser) {

                $workData = WorkRepositoryData::createFromArray([
                    'user' => $paginationUser,
                    'supervisor' => $user,
                    'type' => $type,
                    'workStatus' => $workStatus
                ]);

                $paginationUserWorks = $this->workFacade->getWorksByAuthorSupervisorStatus($workData);

                if ($works->get($paginationUser->getId()) === null) {
                    $works->set($paginationUser->getId(), $paginationUserWorks);
                }

                $workStatusData = WorkStatusRepositoryData::createFromArray([
                    'user' => $paginationUser,
                    'supervisor' => $user,
                    'type' => $type,
                    'workStatus' => $workStatus
                ]);

                $workStatusCount = $this->workStatusFacade->getCountByUser($workStatusData);

                if ($userStatusWorkCounts->get($paginationUser->getId()) === null) {
                    $userStatusWorkCounts->set($paginationUser->getId(), $workStatusCount);
                }
            }
        }

        $this->seoPageService->addTitle($title);

        return $this->twigRenderService->render('user/user_list.html.twig', [
            'type' => $type,
            'title' => $title,
            'users' => $pagination,
            'userWorks' => $works,
            'userStatusWorkCounts' => $userStatusWorkCounts,
            'form' => $form->createView(),
            'openSearchTab' => $openSearchTab,
            'showSearchTab' => $showSearchTab,
            'userHelper' => new UserHelper
        ]);
    }
}
