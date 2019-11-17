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

use Exception;
use FinalWork\FinalWorkBundle\Constant\MediaTypeConstant;
use FinalWork\FinalWorkBundle\Entity\MediaType;
use LogicException;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\Security\Core\Exception\{
    AccessDeniedException,
    AuthenticationCredentialsNotFoundException
};

class AdminController extends CRUDController
{
    /**
     * Returns media list for popup window
     *
     * @param Request $request
     * @return Response
     * @throws AuthenticationCredentialsNotFoundException
     * @throws LogicException
     * @throws AccessDeniedException
     * @throws Exception
     */
    public function showCkImageBrowserAction(Request $request): Response
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
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

            return $this->renderWithExtraParams('@FinalWork/admin/media/media_browser_ck.html.twig', [
                'pagination' => $pagination,
                'id' => random_int(0, 999999999)
            ]);
        }

        throw $this->createAccessDeniedException();
    }
}

