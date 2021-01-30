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

namespace App\Controller;

use App\Exception\ConstantNotFoundException;
use App\Constant\{
    FlashTypeConstant,
    AjaxJsonTypeConstant
};
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse
};

class BaseController extends AbstractController
{
    protected function createPagination(
        Request $request,
        $target,
        int $page = null,
        int $limit = null,
        array $options = null
    ): PaginationInterface {
        return $this->get('app.paginator')->createPagination(
            $request,
            $target,
            $page ?? $this->getParam('pagination.default.page'),
            $limit ?? $this->getParam('pagination.default.limit'),
            $options
        );
    }

    protected function createDeleteForm($entity, string $route): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl($route, [
                'id' => $this->hashIdEncode($entity->getId())
            ]))
            ->setMethod(Request::METHOD_DELETE)
            ->getForm();
    }

    protected function trans(string $translation, array $option = [], string $domain = null): string
    {
        return $this->get('app.translator')->trans($translation, $option, $domain);
    }

    protected function hashIdEncode(int $id): string
    {
        return $this->get('danilovl.hashids')->encode($id);
    }

    protected function hashIdDecode(string $id): array
    {
        return $this->get('danilovl.hashids')->decode($id);
    }

    protected function flushEntity(object $entity = null): void
    {
        $this->get('app.entity_manager')->flush($entity);
    }

    protected function persistAndFlush(object $entity): void
    {
        $this->get('app.entity_manager')->persistAndFlush($entity);
    }

    protected function createEntity(object $entity): void
    {
        $this->get('app.entity_manager')->create($entity);
    }

    protected function removeEntity(object $entity): void
    {
        $this->get('app.entity_manager')->remove($entity);
    }

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
                        FlashTypeConstant::SUCCESS => $this->trans('app.flash.form.create.success')
                    ]
                ];
                break;
            case AjaxJsonTypeConstant::CREATE_FAILURE:
                $data = [
                    'valid' => false,
                    'notifyMessage' => [
                        FlashTypeConstant::ERROR => $this->trans('app.flash.form.create.error'),
                        FlashTypeConstant::WARNING => $this->trans('app.flash.form.create.warning')
                    ],
                ];
                break;
            case AjaxJsonTypeConstant::SAVE_SUCCESS:
                $data = [
                    'valid' => true,
                    'notifyMessage' => [
                        FlashTypeConstant::SUCCESS => $this->trans('app.flash.form.save.success'),
                    ]
                ];
                break;
            case AjaxJsonTypeConstant::SAVE_FAILURE:
                $data = [
                    'valid' => false,
                    'notifyMessage' => [
                        FlashTypeConstant::ERROR => $this->trans('app.flash.form.save.error'),
                        FlashTypeConstant::WARNING => $this->trans('app.flash.form.save.warning')
                    ]
                ];
                break;
            case AjaxJsonTypeConstant::DELETE_SUCCESS:
                $data = [
                    'delete' => true,
                    'notifyMessage' => [
                        FlashTypeConstant::SUCCESS => $this->trans('app.flash.form.delete.success')
                    ]
                ];
                break;
            case AjaxJsonTypeConstant::DELETE_FAILURE:
                $data = [
                    'delete' => false,
                    'notifyMessage' => [
                        FlashTypeConstant::ERROR => $this->trans('app.flash.form.delete.error'),
                        FlashTypeConstant::WARNING => $this->trans('app.flash.form.delete.warning')
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

    protected function getUser(): ?User
    {
        return $this->get('app.user')->getUser();
    }

    protected function getReference(string $entityName, int $id)
    {
        return $this->get('app.entity_manager')->getReference($entityName, $id);
    }

    protected function getRepository(string $entityName)
    {
        return $this->get('app.entity_manager')->getRepository($entityName);
    }

    protected function getParam(string $key)
    {
        return $this->get('danilovl.parameter')->get($key);
    }

    protected function addFlashTrans(string $type, string $trans): void
    {
        $this->addFlash($type, $this->trans($trans));
    }
}