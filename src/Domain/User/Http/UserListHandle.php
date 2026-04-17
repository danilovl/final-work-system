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

use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\User\Bus\Query\UserList\{
    GetUserListQuery,
    GetUserListQueryResult
};
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Infrastructure\Service\{
    SeoPageService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\User\Helper\UserHelper;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\DTO\Repository\WorkRepositoryDTO;
use App\Domain\Work\Facade\WorkFacade;
use App\Domain\Work\Form\WorkSearchStatusForm;
use App\Domain\WorkStatus\DTO\Repository\WorkStatusRepositoryDTO;;
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
        private FormFactoryInterface $formFactory,
        private WorkFacade $workFacade,
        private WorkStatusFacade $workStatusFacade,
        private SeoPageService $seoPageService,
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(Request $request, string $type): Response
    {
        $user = $this->userService->getUser();

        $openSearchTab = false;
        $showSearchTab = true;
        $workStatus = null;

        $form = $this->formFactory
            ->create(WorkSearchStatusForm::class)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            $openSearchTab = true;

            if ($form->isValid()) {
                /** @var WorkStatus[] $workStatus */
                $workStatus = $form->get('status')->getData();
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
                $title = $this->translatorService->trans('app.text.unused_user_list');

                break;
        }

        $query = GetUserListQuery::create(
            request: $request,
            user: $user,
            type: $type,
            workStatus: $workStatus
        );

        /** @var GetUserListQueryResult $result */
        $result = $this->queryBus->handle($query);

        $works = new ArrayCollection;
        $userStatusWorkCounts = new ArrayCollection;

        if ($getUserWorkAndStatus === true) {
            foreach ($result->users as $paginationUser) {
                $workRepositoryDTO = new WorkRepositoryDTO(
                    user: $paginationUser,
                    supervisor: $user,
                    type: $type,
                    workStatus: $workStatus
                );

                $paginationUserWorks = $this->workFacade->listByAuthorSupervisorStatus($workRepositoryDTO);
                $works->set($paginationUser->getId(), $paginationUserWorks);

                $workStatusData = new WorkStatusRepositoryDTO(
                    user: $paginationUser,
                    supervisor: $user,
                    type: $type,
                    workStatus: $workStatus
                );

                $workStatusCount = $this->workStatusFacade->listCountByUser($workStatusData);
                $userStatusWorkCounts->set($paginationUser->getId(), $workStatusCount);
            }
        }

        $this->seoPageService->addTitle($title);

        return $this->twigRenderService->renderToResponse('domain/user/user_list.html.twig', [
            'type' => $type,
            'title' => $title,
            'users' => $result->users,
            'userWorks' => $works,
            'userStatusWorkCounts' => $userStatusWorkCounts,
            'form' => $form->createView(),
            'openSearchTab' => $openSearchTab,
            'showSearchTab' => $showSearchTab,
            'userHelper' => new UserHelper
        ]);
    }
}
