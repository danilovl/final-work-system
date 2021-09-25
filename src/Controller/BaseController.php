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

use Doctrine\Persistence\ObjectRepository;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends AbstractController
{
    protected function createPagination(
        Request $request,
        mixed $target,
        int $page = null,
        int $limit = null,
        array $options = null
    ): PaginationInterface {
        return $this->get('app.paginator')->createPaginationRequest(
            $request,
            $target,
            $page ?? $this->getParam('pagination.default.page'),
            $limit ?? $this->getParam('pagination.default.limit'),
            $options
        );
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

    protected function getUser(): ?User
    {
        return $this->get('app.user')->getUser();
    }

    protected function getReference(string $entityName, int $id): ?object
    {
        return $this->get('app.entity_manager')->getReference($entityName, $id);
    }

    protected function getRepository(string $entityName): ObjectRepository
    {
        return $this->get('app.entity_manager')->getRepository($entityName);
    }

    protected function getParam(string $key): mixed
    {
        return $this->get('danilovl.parameter')->get($key);
    }

    protected function addFlashTrans(string $type, string $trans): void
    {
        $this->addFlash($type, $this->trans($trans));
    }
}