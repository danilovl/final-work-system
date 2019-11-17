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

namespace FinalWork\FinalWorkBundle\Controller\Admin;

use FinalWork\FinalWorkBundle\Constant\MediaTypeConstant;
use FinalWork\FinalWorkBundle\Entity\MediaType;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse
};
use Symfony\Component\Security\Core\Exception\{
    AccessDeniedException,
    BadCredentialsException,
    AuthenticationCredentialsNotFoundException
};

class AdminAjaxController extends Controller
{
    /**
     * Admin action to load media object and generate autocomplete template
     *
     * @param Request $request
     * @return Response
     * @throws AccessDeniedException
     * @throws BadCredentialsException
     * @throws AuthenticationCredentialsNotFoundException
     * @throws LogicException
     */
    public function getMediaObjectForAutocompleteAction(Request $request): Response
    {
        if ($request->isXmlHttpRequest() && $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $id = $request->request->get('id');

            if ($id !== null) {
                $object = $this->get('final_work.facade.media')
                    ->find($id);

                return $this->render('@FinalWork/admin/form/sonata_type_model_autocomplete/image.html.twig', [
                    'entity' => $object,
                ]);
            }
        }

        throw $this->createAccessDeniedException();
    }

    /**
     * Returns media list for modal window
     *
     * @param Request $request
     * @return Response
     * @throws AuthenticationCredentialsNotFoundException
     * @throws LogicException
     * @throws AccessDeniedException
     */
    public function getMediaListAction(Request $request): Response
    {
        if ($request->isXmlHttpRequest() &&
            $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')
        ) {
            $idUnique = $request->get('idUnique');
            $page = $request->get('page', 1);

            $mediasQuery = $this->get('final_work.facade.media')
                ->getMediasByType(
                    $this->getDoctrine()
                        ->getReference(MediaType::class, MediaTypeConstant::ARTICLE)
                );

            $paginator = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $mediasQuery,
                $request->query->getInt('page', $page),
                $request->query->getInt('limit', 20)
            );

            return $this->render('@FinalWork/admin/media/media_list.html.twig', [
                'idUnique' => $idUnique,
                'pagination' => $pagination,
            ]);
        }

        throw $this->createAccessDeniedException();
    }

    /**
     * Helper returns JSON response to call media select action in admin
     *
     * @param Request $request
     * @return JsonResponse
     * @throws AuthenticationCredentialsNotFoundException
     * @throws LogicException
     * @throws AccessDeniedException
     */
    public function getMediaResponseAction(Request $request): JsonResponse
    {
        $id = $request->request->get('id');
        $targetId = $request->request->get('targetId');

        if ($request->isXmlHttpRequest() &&
            $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && $id !== null
        ) {
            $object = $this->get('final_work.facade.media')
                ->find($id);

            if ($object !== null) {
                $return = [
                    'objectId' => (int)$id,
                    'id' => (int)$id,
                    'targetId' => $targetId,
                    'result' => 'ok',
                ];

                return new JsonResponse($return);
            }
        }

        throw $this->createAccessDeniedException();
    }
}

