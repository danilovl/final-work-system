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

namespace App\Application\Service;

use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

readonly class PaginatorService
{
    public function __construct(
        private PaginatorInterface $paginator,
        private ParameterServiceInterface $parameterService
    ) {}

    public function createPagination(
        mixed $target,
        int $page,
        int $limit = null,
        array $options = []
    ): PaginationInterface {
        return $this->paginator->paginate(
            $target,
            $page,
            $limit,
            $options
        );
    }

    public function createPaginationRequest(
        Request $request,
        mixed $target,
        int $page = null,
        int $limit = null,
        array $options = null
    ): PaginationInterface {
        $page = $page ?? $this->parameterService->getInt('pagination.default.page');
        $limit = $limit ?? $this->parameterService->getInt('pagination.default.limit');

        $defaultPageName = 'page';
        $defaultLimitName = 'limit';

        if (isset($options['pageParameterName'])) {
            $defaultPageName = $options['pageParameterName'];
        }

        return $this->createPagination(
            $target,
            $request->query->getInt($defaultPageName, $page),
            $request->query->getInt($defaultLimitName, $limit),
            $options !== null ? $options : []
        );
    }
}
