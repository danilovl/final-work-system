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

namespace FinalWork\FinalWorkBundle\Controller\Ajax;

use FinalWork\FinalWorkBundle\Constant\{
    AjaxJsonTypeConstant,
    VoterSupportConstant
};
use FinalWork\FinalWorkBundle\Model\User\UserModel;
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};
use FinalWork\FinalWorkBundle\Controller\BaseController;
use FinalWork\FinalWorkBundle\Entity\Work;
use FinalWork\FinalWorkBundle\Model\Work\WorkModel;
use FinalWork\FinalWorkBundle\Form\{
    UserForm,
    WorkForm
};
use FinalWork\FinalWorkBundle\Helper\FormValidationMessageHelper;
use LogicException;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\Exception\InvalidArgumentException;

class WorkController extends BaseController
{
    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAction(Request $request): JsonResponse
    {
        $user = $this->getUser();

        $workModel = new WorkModel;
        $workModel->supervisor = $user;

        $form = $this->createForm(WorkForm::class, $workModel, ['user' => $user])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $work = $this->get('final_work.factory.work')
                ->flushFromModel($workModel);

            $this->get('final_work.event_dispatcher.work')
                ->onWorkCreate($work);

            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @param Request $request
     * @param Work $work
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(
        Request $request,
        Work $work
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        $workModel = WorkModel::fromWork($work);
        $form = $this->createForm(WorkForm::class, $workModel, ['user' => $this->getUser()])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('final_work.factory.work')
                ->flushFromModel($workModel, $work);

            $this->get('final_work.event_dispatcher.work')
                ->onWorkEdit($work);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @param Request $request
     * @param Work $work
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAuthorAction(
        Request $request,
        Work $work
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        $author = $work->getAuthor();
        $userModel = UserModel::fromUser($author);

        $form = $this->createForm(UserForm::class, $userModel)
            ->remove('username')
            ->remove('role')
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('final_work.factory.user')
                ->flushFromModel($userModel, $author);

            $this->get('final_work.event_dispatcher.work')
                ->onWorkEditAuthor($work);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @param Work $work
     * @return JsonResponse
     *
     * @throws InvalidArgumentException
     * @throws AccessDeniedException
     * @throws LogicException
     */
    public function deleteAction(Work $work): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $work);

        $this->removeEntity($work);

        return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
