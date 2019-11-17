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

namespace FinalWork\FinalWorkBundle\Controller;

use Doctrine\ORM\{
    Query,
    ORMException,
    OptimisticLockException
};
use Doctrine\Common\Collections\Collection;
use FinalWork\FinalWorkBundle\Exception\ConstantNotFoundException;
use FinalWork\FinalWorkBundle\Constant\{
    FlashTypeConstant,
    TranslationConstant,
    AjaxJsonTypeConstant
};
use FinalWork\SonataUserBundle\Entity\User;
use LogicException;
use Symfony\Component\Form\FormInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse
};

class BaseController extends Controller
{
    /**
     * @param Request $request
     * @param array|Collection|Query $target
     * @param int $page
     * @param int $limit
     * @param array $options
     * @return PaginationInterface
     *
     * @throws LogicException
     */
    protected function createPagination(
        Request $request,
        $target,
        int $page = null,
        int $limit = null,
        array $options = null
    ): PaginationInterface {
        return $this->get('final_work.paginator')->createPagination(
            $request,
            $target,
            $page ?? $this->getParam('pagination.default.page'),
            $limit ?? $this->getParam('pagination.default.limit'),
            $options
        );
    }

    /**
     * @param $entity
     * @param string $route
     * @return FormInterface
     */
    protected function createDeleteForm($entity, string $route): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl($route, [
                'id' => $this->hashIdEncode($entity->getId())
            ]))
            ->setMethod(Request::METHOD_DELETE)
            ->getForm();
    }

    /**
     * @param string $translation
     * @param array $option
     * @param string|null $domain
     * @return string
     */
    protected function trans(string $translation, array $option = [], string $domain = null): string
    {
        if (strpos($translation, TranslationConstant::FLASH_START_KEY) !== false) {
            $domain = TranslationConstant::FLASH_DOMAIN;
        }

        return $this->get('translator')->trans($translation, $option, $domain);
    }

    /**
     * @param int $id
     * @return string
     */
    protected function hashIdEncode(int $id): string
    {
        return $this->get('hashids')->encode($id);
    }

    /**
     * @param string $id
     * @return array
     */
    protected function hashIdDecode(string $id): array
    {
        return $this->get('hashids')->decode($id);
    }

    /**
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function flushEntity(): void
    {
        $this->get('final_work.entity_manager')->flush();
    }

    /**
     * @param $entity
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function createEntity($entity): void
    {
        $this->get('final_work.entity_manager')->create($entity);
    }

    /**
     * @param $entity
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function removeEntity($entity): void
    {
        $this->get('final_work.entity_manager')->remove($entity);
    }

    /**
     * @param string $type
     * @param array|null $extraData
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function createAjaxJson(
        string $type,
        ?array $extraData = null,
        int $statusCode = Response::HTTP_OK
    ): JsonResponse {
        switch ($type) {
            case AjaxJsonTypeConstant::CREATE_SUCCESS:
                $data = [
                    'valid' => true,
                    'notifyMessage' => [
                        FlashTypeConstant::SUCCESS => $this->trans('finalwork.flash.form.create.success')
                    ]
                ];
                break;
            case AjaxJsonTypeConstant::CREATE_FAILURE:
                $data = [
                    'valid' => false,
                    'notifyMessage' => [
                        FlashTypeConstant::ERROR => $this->trans('finalwork.flash.form.create.error'),
                        FlashTypeConstant::WARNING => $this->trans('finalwork.flash.form.create.warning')
                    ],
                ];
                break;
            case AjaxJsonTypeConstant::SAVE_SUCCESS:
                $data = [
                    'valid' => true,
                    'notifyMessage' => [
                        FlashTypeConstant::SUCCESS => $this->trans('finalwork.flash.form.save.success'),
                    ]
                ];
                break;
            case AjaxJsonTypeConstant::SAVE_FAILURE:
                $data = [
                    'valid' => false,
                    'notifyMessage' => [
                        FlashTypeConstant::ERROR => $this->trans('finalwork.flash.form.save.error'),
                        FlashTypeConstant::WARNING => $this->trans('finalwork.flash.form.save.warning')
                    ]
                ];
                break;
            case AjaxJsonTypeConstant::DELETE_SUCCESS:
                $data = [
                    'delete' => true,
                    'notifyMessage' => [
                        FlashTypeConstant::SUCCESS => $this->trans('finalwork.flash.form.delete.success')
                    ]
                ];
                break;
            case AjaxJsonTypeConstant::DELETE_FAILURE:
                $data = [
                    'delete' => false,
                    'notifyMessage' => [
                        FlashTypeConstant::ERROR => $this->trans('finalwork.flash.form.delete.error'),
                        FlashTypeConstant::WARNING => $this->trans('finalwork.flash.form.delete.warning')
                    ]
                ];
                break;
            default:
                throw new ConstantNotFoundException('AjaxJson constant type not found');
        }

        if (!empty($extraData)) {
            $data = array_merge($data, $extraData);
        }

        return new JsonResponse($data, $statusCode);
    }

    /**
     * @param Request $request
     * @param string $templateName
     * @return string
     */
    protected function ajaxOrNormalFolder(Request $request, string $templateName): string
    {
        $file = basename($templateName);
        $fileAjax = sprintf('%s%s', $this->getParam('template.ajax'), $file);
        $templateNameAjax = str_replace($file, $fileAjax, $templateName);

        if ($request->isXmlHttpRequest()) {
            return $templateNameAjax;
        }

        return $templateName;
    }

    /**
     * @return User
     */
    protected function getUser(): User
    {
        return parent::getUser();
    }

    /**
     * @param string $entityName
     * @param int $id
     * @return mixed
     * @throws ORMException
     */
    protected function getReference(string $entityName, int $id)
    {
        return $this->get('final_work.entity_manager')->getReference($entityName, $id);
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getParam(string $key)
    {
        return $this->get('final_work.parameters')->getParam($key);
    }

    /**
     * @param string $type
     * @param string $trans
     */
    protected function addFlashTrans(string $type, string $trans): void
    {
        $this->addFlash($type, $this->trans($trans));
    }
}