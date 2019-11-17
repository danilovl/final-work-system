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

namespace FinalWork\FinalWorkBundle\Services;

use FinalWork\FinalWorkBundle\Exception\RuntimeException;
use Doctrine\ORM\Query;
use FinalWork\FinalWorkBundle\Constant\TabTypeConstant;
use FinalWork\FinalWorkBundle\Entity\{
    Work,
    Task,
    Event,
    Media,
    ConversationMessage
};
use FinalWork\SonataUserBundle\Entity\User;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\{
    AbstractPagination,
    PaginationInterface
};
use Symfony\Component\HttpFoundation\Request;

class WorkDetailTabService
{
    /**
     * @var string
     */
    private $activeTab;

    /**
     * @var EntityManagerService
     */
    private $em;

    /**
     * @var PaginatorService
     */
    private $paginator;

    /**
     * @var ParametersService
     */
    private $parametersService;

    /**
     * WorkDetailTab constructor.
     * @param EntityManagerService $entityManagerService
     * @param PaginatorService $paginator
     * @param ParametersService $parametersService
     */
    public function __construct(
        EntityManagerService $entityManagerService,
        PaginatorService $paginator,
        ParametersService $parametersService
    ) {
        $this->em = $entityManagerService;
        $this->paginator = $paginator;
        $this->parametersService = $parametersService;
    }

    /**
     * @return string
     */
    public function getActiveTab(): string
    {
        return $this->activeTab ?? TabTypeConstant::TAB_TASK;
    }

    /**
     * @param string|null $activeTab
     * @return WorkDetailTabService
     */
    public function setActiveTab(?string $activeTab): self
    {
        if (in_array($activeTab, TabTypeConstant::TABS, true)) {
            $this->activeTab = $activeTab;
        }

        return $this;
    }

    /**
     * @param Request $request
     * @param string $tab
     * @param Work $work
     * @param User|null $user
     * @return PaginationInterface
     */
    public function getTabPagination(
        Request $request,
        string $tab,
        Work $work,
        ?User $user = null
    ): PaginationInterface {
        $paginator = $this->paginator->createPagination(
            $request,
            $this->getQueryPagination($tab, $work, $user),
            $this->getPaginationPage($tab),
            $this->getPaginationLimit($tab),
            $this->getOptions($tab)
        );

        $this->setActiveTabByPagination($tab, $paginator);

        return $paginator;
    }

    /**
     * @param string $tab
     * @return int
     */
    private function getPaginationPage(string $tab): int
    {
        $page = $this->parametersService->getParam("pagination.work_detail_tab.{$tab}.page");
        if ($page === null) {
            $page = $this->parametersService->getParam("pagination.default.page");
        }

        return $page;
    }

    /**
     * @param string $tab
     * @return int
     */
    private function getPaginationLimit(string $tab): int
    {
        $limit = $this->parametersService->getParam("pagination.work_detail_tab.{$tab}.limit");
        if ($limit === null) {
            $limit = $this->parametersService->getParam("pagination.default.limit");
        }

        return $limit;
    }

    /**
     * @param string $tab
     * @param PaginationInterface|AbstractPagination $pagination
     */
    private function setActiveTabByPagination(
        string $tab,
        PaginationInterface $pagination
    ): void {
        if ($pagination->getTotalItemCount() > 0 && $this->activeTab === null) {
            $this->setActiveTab($tab);
        }
    }

    /**
     * @param string $tab
     * @param Work $work
     * @param User|null $user
     * @return Query
     */
    private function getQueryPagination(
        string $tab,
        Work $work,
        ?User $user = null
    ): Query {
        $queryPagination = null;

        switch ($tab) {
            case TabTypeConstant::TAB_TASK:
                $queryPagination = $this->em
                    ->getRepository(Task::class)
                    ->findAllByWork($work, $work->isSupervisor($user) ? false : true)
                    ->getQuery();
                break;
            case TabTypeConstant::TAB_VERSION:
                $queryPagination = $this->em
                    ->getRepository(Media::class)
                    ->findAllByWork($work)
                    ->getQuery();
                break;
            case TabTypeConstant::TAB_EVENT:
                $queryPagination = $this->em
                    ->getRepository(Event::class)
                    ->findAllByWork($work)
                    ->getQuery();
                break;
            case TabTypeConstant::TAB_MESSAGE:
                $queryPagination = $this->em
                    ->getRepository(ConversationMessage::class)
                    ->findAllByWorkUser($work, $user)
                    ->getQuery();
                break;
        }

        if ($queryPagination === null) {
            throw new RuntimeException('Query for tab pagination was not created');
        }

        return $queryPagination;
    }

    /**
     * @param string $prefix
     * @return array
     */
    private function getOptions(string $prefix): array
    {
        return [
            PaginatorInterface::PAGE_PARAMETER_NAME => 'page_' . $prefix,
            PaginatorInterface::SORT_FIELD_PARAMETER_NAME => 'sort_' . $prefix,
            PaginatorInterface::SORT_DIRECTION_PARAMETER_NAME => 'direction_' . $prefix,
            PaginatorInterface::FILTER_FIELD_PARAMETER_NAME => 'filterParam_' . $prefix,
            PaginatorInterface::FILTER_VALUE_PARAMETER_NAME => 'filterValue_' . $prefix,
            PaginatorInterface::DISTINCT => true
        ];
    }
}