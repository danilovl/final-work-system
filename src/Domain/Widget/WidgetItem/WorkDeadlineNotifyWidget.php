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

namespace App\Domain\Widget\WidgetItem;

use App\Infrastructure\Service\{
    TranslatorService,
    TwigRenderService
};
use App\Domain\User\Helper\UserRoleHelper;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\DTO\Repository\WorkRepositoryDTO;
use App\Domain\Work\Helper\WorkDeadlineHelper;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\Work\Bus\Query\WorksByAuthorStatus\{GetWorksByAuthorStatusQuery, GetWorksByAuthorStatusQueryResult};

class WorkDeadlineNotifyWidget extends BaseWidget
{
    public function __construct(
        private readonly UserService $userService,
        private readonly ParameterServiceInterface $parameterService,
        private readonly TranslatorService $translatorService,
        private readonly TwigRenderService $twigRenderService,
        private readonly QueryBusInterface $queryBus
    ) {}

    public function render(): ?string
    {
        $user = $this->userService->getUser();
        if (!UserRoleHelper::isAuthor($user)) {
            return null;
        }

        $workRepositoryDTO = new WorkRepositoryDTO(
            user: $user,
            supervisor: null,
            type: WorkUserTypeConstant::AUTHOR->value,
            workStatus: [WorkStatusConstant::ACTIVE->value]
        );

        $getWorksByAuthorStatusQuery = GetWorksByAuthorStatusQuery::create($workRepositoryDTO);
        /** @var GetWorksByAuthorStatusQueryResult $getWorksByAuthorStatusQueryResult */
        $getWorksByAuthorStatusQueryResult = $this->queryBus->handle($getWorksByAuthorStatusQuery);
        $works = $getWorksByAuthorStatusQueryResult->works;
        if (count($works) === 0) {
            return null;
        }

        $type = null;
        foreach ($works as $work) {
            $deadlineDays = WorkDeadlineHelper::getDeadlineDays($work);
            if ($deadlineDays < 0 || $deadlineDays > 30) {
                continue;
            }

            $type = $deadlineDays < 10 ? 'homepage_notify.type_class.danger' : 'homepage_notify.type_class.warning';
        }

        if ($type === null) {
            return null;
        }

        return $this->twigRenderService->render('application/widget/notify.html.twig', [
            'class' => $this->parameterService->getString($type),
            'message' => $this->translatorService->trans('app.text.reminding_work_deadline_submission')
        ]);
    }
}
