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

namespace App\Infrastructure\Service;

use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

readonly class PaginatorService
{
    public function __construct(
        private PaginatorInterface $paginator,
        private ParameterServiceInterface $parameterService,
        private EntityManagerService $entityManagerService
    ) {}

    public function createPagination(
        mixed $target,
        int $page,
        ?int $limit = null,
        array $options = [],
        bool $detachEntity = false
    ): PaginationInterface {
        $pagination = $this->paginator->paginate(
            $target,
            $page,
            $limit,
            $options
        );

        if ($detachEntity) {
            /** @var object[] $items */
            $items = iterator_to_array($pagination->getItems());
            $this->entityManagerService->detachArray($items);
        }

        return $pagination;
    }

    public function createPaginationRequest(
        Request $request,
        mixed $target,
        ?int $page = null,
        ?int $limit = null,
        ?array $options = null,
        bool $detachEntity = false
    ): PaginationInterface {
        $page ??= $this->parameterService->getInt('pagination.default.page');
        $limit ??= $this->parameterService->getInt('pagination.default.limit');

        $defaultPageName = 'page';
        $defaultLimitName = 'limit';

        if (isset($options['pageParameterName'])) {
            $defaultPageName = $options['pageParameterName'];
        }

        $pagination = $this->createPagination(
            $target,
            $request->query->getInt($defaultPageName, $page),
            $request->query->getInt($defaultLimitName, $limit),
            $options !== null ? $options : []
        );

        if ($detachEntity) {
            /** @var object[] $items */
            $items = iterator_to_array($pagination->getItems());
            $this->entityManagerService->detachArray($items);
        }

        return $pagination;
    }
}
