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

namespace App\Application\Widget;

use App\Application\Service\{
    TranslatorService,
    TwigRenderService
};
use App\Domain\User\Helper\UserRoleHelper;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\DataTransferObject\WorkRepositoryData;
use App\Domain\Work\Facade\WorkFacade;
use App\Domain\Work\Service\WorkService;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;

class WorkDeadlineNotifyWidget extends BaseWidget
{
    public function __construct(
        private readonly UserService $userService,
        private readonly WorkService $workService,
        private readonly ParameterServiceInterface $parameterService,
        private readonly TranslatorService $translatorService,
        private readonly TwigRenderService $twigRenderService,
        private readonly WorkFacade $workFacade
    ) {}

    public function render(): ?string
    {
        $user = $this->userService->getUser();
        if (!UserRoleHelper::isAuthor($user)) {
            return null;
        }

        $workData = WorkRepositoryData::createFromArray([
            'user' => $user,
            'supervisor' => null,
            'type' => WorkUserTypeConstant::AUTHOR->value,
            'workStatus' => [WorkStatusConstant::ACTIVE->value]
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

        return $this->twigRenderService->render('widget/notify.html.twig', [
            'class' => $this->parameterService->getString($type),
            'message' => $this->translatorService->trans('app.text.reminding_work_deadline_submission')
        ]);
    }
}
