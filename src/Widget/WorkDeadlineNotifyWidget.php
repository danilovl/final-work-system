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

namespace App\Widget;

use App\DataTransferObject\Repository\WorkData;
use App\Helper\UserRoleHelper;
use App\Constant\{
    WorkStatusConstant,
    WorkUserTypeConstant
};
use App\Model\Work\WorkFacade;
use Danilovl\ParameterBundle\Services\ParameterService;
use App\Service\{
    UserService,
    WorkService,
    TranslatorService
};
use Twig\Environment;

class WorkDeadlineNotifyWidget extends BaseWidget
{
    public function __construct(
        private UserService $userService,
        private WorkService $workService,
        private ParameterService $parameterService,
        private TranslatorService $translatorService,
        private Environment $twig,
        private WorkFacade $workFacade
    ) {
    }

    public function render(): ?string
    {
        $user = $this->userService->getUser();
        if (!UserRoleHelper::isAuthor($user)) {
            return null;
        }

        $workData = WorkData::createFromArray([
            'user' => $user,
            'supervisor' => null,
            'type' => WorkUserTypeConstant::AUTHOR,
            'workStatus' => [WorkStatusConstant::ACTIVE]
        ]);

        $works = $this->workFacade->getWorksByAuthorStatus($workData);
        if (count($works) === 0) {
            return null;
        }

        $type = null;
        foreach ($works as $work) {
            $deadlineDays = $this->workService->getDeadlineDays($work);
            if ($deadlineDays < 0 || $deadlineDays > 30) {
                continue;
            }

            $type = $deadlineDays < 10 ? 'homepage_notify.type_class.danger' : 'homepage_notify.type_class.warning';
        }

        if ($type === null) {
            return null;
        }

        return $this->twig->render('widget/notify.html.twig', [
            'class' => $this->parameterService->get($type),
            'message' => $this->translatorService->trans('app.text.reminding_work_deadline_submission')
        ]);
    }
}
