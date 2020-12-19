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

namespace App\Services;

use App\Exception\RuntimeException;
use Danilovl\ParameterBundle\Services\ParameterService;
use Doctrine\ORM\Query;
use App\Constant\TabTypeConstant;
use App\Entity\{
    Work,
    Task,
    Event,
    Media,
    ConversationMessage
};
use App\Entity\User;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\Request;

class WorkDetailTabService
{
    private ?string $activeTab = null;

    public function __construct(
        private EntityManagerService $em,
        private PaginatorService $paginator,
        private ParameterService $parameterService
    ) {
    }

    public function getActiveTab(): string
    {
        return $this->activeTab ?? TabTypeConstant::TAB_TASK;
    }

    public function setActiveTab(?string $activeTab): self
    {
        if (in_array($activeTab, TabTypeConstant::TABS, true)) {
            $this->activeTab = $activeTab;
        }

        return $this;
    }

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

    private function getPaginationPage(string $tab): int
    {
        $page = $this->parameterService->get("pagination.work_detail_tab.{$tab}.page");
        if ($page === null) {
            $page = $this->parameterService->get("pagination.default.page");
        }

        return $page;
    }

    private function getPaginationLimit(string $tab): int
    {
        $limit = $this->parameterService->get("pagination.work_detail_tab.{$tab}.limit");
        if ($limit === null) {
            $limit = $this->parameterService->get("pagination.default.limit");
        }

        return $limit;
    }

    private function setActiveTabByPagination(
        string $tab,
        PaginationInterface $pagination
    ): void {
        if ($pagination->getTotalItemCount() > 0 && $this->activeTab === null) {
            $this->setActiveTab($tab);
        }
    }

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
                    ->allByWork($work, $work->isSupervisor($user) ? false : true)
                    ->getQuery();
                break;
            case TabTypeConstant::TAB_VERSION:
                $queryPagination = $this->em
                    ->getRepository(Media::class)
                    ->allByWork($work)
                    ->getQuery();
                break;
            case TabTypeConstant::TAB_EVENT:
                $queryPagination = $this->em
                    ->getRepository(Event::class)
                    ->allByWork($work)
                    ->getQuery();
                break;
            case TabTypeConstant::TAB_MESSAGE:
                $queryPagination = $this->em
                    ->getRepository(ConversationMessage::class)
                    ->allByWorkUser($work, $user)
                    ->getQuery();
                break;
        }

        if ($queryPagination === null) {
            throw new RuntimeException('Query for tab pagination was not created');
        }

        return $queryPagination;
    }

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