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

use App\Domain\Work\Model\WorkSearchStatusModel;
use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
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
use App\Domain\Work\Form\WorkSearchStatusForm;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use App\Domain\Work\Bus\Query\WorksByUsersSupervisorStatus\{GetWorksByUsersSupervisorStatusQuery, GetWorksByUsersSupervisorStatusQueryResult};
use App\Domain\WorkStatus\Bus\Query\WorkStatusCountsByUsers\{GetWorkStatusCountsByUsersQuery, GetWorkStatusCountsByUsersQueryResult};

readonly class UserListHandle
{
    public function __construct(
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private UserService $userService,
        private FormFactoryInterface $formFactory,
        private SeoPageService $seoPageService,
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(Request $request, string $type): Response
    {
        $user = $this->userService->getUser();

        $openSearchTab = false;
        $showSearchTab = true;
        $workStatus = null;

        $workSearchStatusModel = new WorkSearchStatusModel;
        $form = $this->formFactory
            ->create(WorkSearchStatusForm::class, $workSearchStatusModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            $openSearchTab = true;

            if ($form->isValid()) {
                /** @var WorkStatus[] $workStatus */
                $workStatus = $workSearchStatusModel->status->toArray();
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
            $userIds = [];
            foreach ($result->users as $paginationUser) {
                $userIds[] = $paginationUser->getId();
            }

            $getWorksByUsersSupervisorStatusQuery = GetWorksByUsersSupervisorStatusQuery::create(
                userIds: $userIds,
                supervisor: $user,
                type: $type,
                workStatus: $workStatus
            );
            /** @var GetWorksByUsersSupervisorStatusQueryResult $getWorksByUsersSupervisorStatusQueryResult */
            $getWorksByUsersSupervisorStatusQueryResult = $this->queryBus->handle($getWorksByUsersSupervisorStatusQuery);
            $worksByUser = $getWorksByUsersSupervisorStatusQueryResult->works;

            $getWorkStatusCountsByUsersQuery = GetWorkStatusCountsByUsersQuery::create(
                userIds: $userIds,
                supervisor: $user,
                type: $type,
                workStatus: $workStatus
            );
            /** @var GetWorkStatusCountsByUsersQueryResult $getWorkStatusCountsByUsersQueryResult */
            $getWorkStatusCountsByUsersQueryResult = $this->queryBus->handle($getWorkStatusCountsByUsersQuery);
            $workStatusCountsByUser = $getWorkStatusCountsByUsersQueryResult->counts;

            foreach ($userIds as $userId) {
                $works->set($userId, $worksByUser[$userId] ?? []);
                $userStatusWorkCounts->set($userId, $workStatusCountsByUser[$userId] ?? []);
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
