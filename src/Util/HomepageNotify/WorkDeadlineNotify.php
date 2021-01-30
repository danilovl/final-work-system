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

namespace App\Util\HomepageNotify;

use App\Helper\UserRoleHelper;
use App\Constant\{
    WorkStatusConstant,
    WorkUserTypeConstant
};
use App\Interfaces\HomepageNotifyInterface;
use App\Model\Work\WorkFacade;
use Danilovl\ParameterBundle\Services\ParameterService;
use App\Service\{UserService, TranslatorService, WorkService};
use Twig\Environment;

class WorkDeadlineNotify extends BaseNotify implements HomepageNotifyInterface
{
    public function __construct(
        protected UserService $userService,
        private WorkService $workService,
        protected ParameterService $parameterService,
        protected TranslatorService $translatorService,
        protected Environment $twig,
        protected WorkFacade $workFacade
    ) {
        parent::__construct($userService, $parameterService, $translatorService, $twig);
    }

    public function renderNotify(): ?string
    {
        $user = $this->userService->getUser();
        if (!UserRoleHelper::isAuthor($user)) {
            return null;
        }

        $works = $this->workFacade->getWorksByAuthorStatus($user, WorkUserTypeConstant::AUTHOR, WorkStatusConstant::ACTIVE);
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

        return $this->twig->render('homepage_notify/notify.html.twig', [
            'class' => $this->parameterService->get($type),
            'message' => $this->translatorService->trans('app.text.reminding_work_deadline_submission')
        ]);
    }
}
