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

namespace App\Service;

use Danilovl\ParameterBundle\Services\ParameterService;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class PaginatorService
{
    public function __construct(
        private PaginatorInterface $paginator,
        private ParameterService $parameterService
    ) {
    }

    public function createPagination(
        Request $request,
        mixed $target,
        int $page = null,
        int $limit = null,
        array $options = null
    ): PaginationInterface {
        $page = $page ?? $this->parameterService->get('pagination.default.page');
        $limit = $limit ?? $this->parameterService->get('pagination.default.limit');

        $defaultPageName = 'page';
        $defaultLimitName = 'limit';

        if ($options !== null) {
            if (isset($options['pageParameterName'])) {
                $defaultPageName = $options['pageParameterName'];
            }
        }

        return $this->paginator->paginate(
            $target,
            $request->query->getInt($defaultPageName, $page),
            $request->query->getInt($defaultLimitName, $limit),
            $options !== null ? $options : []
        );
    }
}
