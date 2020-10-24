<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Controller\Ajax;

use App\Constant\{
    AjaxJsonTypeConstant,
    VoterSupportConstant
};
use App\Model\User\UserModel;
use App\Controller\BaseController;
use App\Entity\Work;
use App\Model\Work\WorkModel;
use App\Form\{
    WorkForm,
    UserEditForm
};
use App\Helper\FormValidationMessageHelper;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class WorkController extends BaseController
{
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();

        $workModel = new WorkModel;
        $workModel->supervisor = $user;

        $form = $this->createForm(WorkForm::class, $workModel, ['user' => $user])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $work = $this->get('app.factory.work')
                ->flushFromModel($workModel);

            $this->get('app.event_dispatcher.work')
                ->onWorkCreate($work);

            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    public function edit(
        Request $request,
        Work $work
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        $workModel = WorkModel::fromWork($work);
        $form = $this->createForm(WorkForm::class, $workModel, ['user' => $this->getUser()])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.factory.work')
                ->flushFromModel($workModel, $work);

            $this->get('app.event_dispatcher.work')
                ->onWorkEdit($work);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    public function editAuthor(
        Request $request,
        Work $work
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        $author = $work->getAuthor();
        $userModel = UserModel::fromUser($author);

        $form = $this->createForm(UserEditForm::class, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.factory.user')
                ->flushFromModel($userModel, $author);

            $this->get('app.event_dispatcher.work')
                ->onWorkEditAuthor($work);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    public function delete(Work $work): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $work);

        $this->removeEntity($work);

        return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
