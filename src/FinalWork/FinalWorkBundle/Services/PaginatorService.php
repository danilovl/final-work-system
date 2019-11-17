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

namespace FinalWork\FinalWorkBundle\Services;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class PaginatorService
{
    /**
     * @var PaginatorInterface
     */
    private $pagination;

    /**
     * @var ParametersService
     */
    private $parametersService;

    /**
     * Paginator constructor.
     * @param PaginatorInterface $pagination
     * @param ParametersService $parametersService
     */
    public function __construct(
        PaginatorInterface $pagination,
        ParametersService $parametersService
    ) {
        $this->pagination = $pagination;
        $this->parametersService = $parametersService;
    }

    /**
     * @param Request $request
     * @param array|Collection|Query $target
     * @param int $page
     * @param int $limit
     * @param array $options
     * @return PaginationInterface
     *
     */
    public function createPagination(
        Request $request,
        $target,
        int $page = null,
        int $limit = null,
        array $options = null
    ): PaginationInterface {
        $page = $page ?? $this->parametersService->getParam('pagination.default.page');
        $limit = $limit ?? $this->parametersService->getParam('pagination.default.limit');

        $defaultPageName = 'page';
        $defaultLimitName = 'limit';

        if ($options !== null) {
            $this->pagination->setDefaultPaginatorOptions($options);

            if (isset($options['pageParameterName'])) {
                $defaultPageName = $options['pageParameterName'];
            }
        }

        $pagination = $this->pagination->paginate(
            $target,
            $request->query->getInt($defaultPageName, $page),
            $request->query->getInt($defaultLimitName, $limit)
        );

        return $pagination;
    }
}
