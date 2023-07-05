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

namespace App\Domain\Work\Service;

use App\Application\Constant\TabTypeConstant;
use App\Application\Exception\RuntimeException;
use App\Application\Service\{
    PaginatorService,
    EntityManagerService
};
use App\Application\Helper\{
    WorkRoleHelper,
    ConversationHelper
};
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\Event\Entity\Event;
use App\Domain\Media\Entity\Media;
use App\Domain\Task\Entity\Task;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Doctrine\ORM\Query;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class WorkDetailTabService
{
    private ?string $activeTab = null;

    public function __construct(
        private readonly EntityManagerService $entityManagerService,
        private readonly PaginatorService $paginator,
        private readonly ParameterServiceInterface $parameterService
    ) {}

    public function getActiveTab(): string
    {
        return $this->activeTab ?? TabTypeConstant::TAB_TASK->value;
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
        $paginator = $this->paginator->createPaginationRequest(
            $request,
            $this->getQueryPagination($tab, $work, $user),
            $this->getPaginationPage($tab),
            $this->getPaginationLimit($tab),
            $this->getOptions($tab)
        );

        $this->setActiveTabByPagination($tab, $paginator);

        if ($tab === TabTypeConstant::TAB_MESSAGE->value) {
            if ($paginator->count() > 0) {
                ConversationHelper::getConversationOpposite(
                    [$paginator[0]->getConversation()],
                    $user
                );
            }
        }

        return $paginator;
    }

    private function getPaginationPage(string $tab): int
    {
        return $this->parameterService->getInt("pagination.work_detail_tab.{$tab}.page");
    }

    private function getPaginationLimit(string $tab): int
    {
        return $this->parameterService->getInt("pagination.work_detail_tab.{$tab}.limit");
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
        $isSupervisor = $user !== null && WorkRoleHelper::isSupervisor($work, $user);

        $queryPagination = match ($tab) {
            TabTypeConstant::TAB_TASK->value => $this->entityManagerService
                ->getRepository(Task::class)
                ->allByWork($work, !$isSupervisor)
                ->getQuery(),
            TabTypeConstant::TAB_VERSION->value => $this->entityManagerService
                ->getRepository(Media::class)
                ->allByWork($work)
                ->getQuery(),
            TabTypeConstant::TAB_EVENT->value => $this->entityManagerService
                ->getRepository(Event::class)
                ->allByWork($work)
                ->getQuery(),
            TabTypeConstant::TAB_MESSAGE->value => $this->entityManagerService
                ->getRepository(ConversationMessage::class)
                ->allByWorkUser($work, $user)
                ->getQuery(),
            default => null
        };

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
