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
use App\Domain\ConversationMessage\Repository\ConversationMessageRepository;
use App\Domain\Event\Repository\EventRepository;
use App\Domain\Media\Repository\MediaRepository;
use App\Domain\Task\Repository\TaskRepository;
use App\Infrastructure\Service\{
    PaginatorService,
    EntityManagerService
};
use App\Domain\Conversation\Helper\ConversationHelper;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\Event\Entity\Event;
use App\Domain\Media\Entity\Media;
use App\Domain\Task\Entity\Task;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Helper\WorkRoleHelper;
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
        ?User $user = null,
        bool $setHydrationMode = false
    ): PaginationInterface {
        $paginator = $this->paginator->createPaginationRequest(
            $request,
            $this->getQueryPagination($tab, $work, $user, $setHydrationMode),
            $this->getPaginationPage($tab),
            $this->getPaginationLimit($tab),
            $this->getOptions($tab)
        );

        $this->setActiveTabByPagination($tab, $paginator);

        if ($tab === TabTypeConstant::TAB_MESSAGE->value) {
            if ($paginator->count() > 0 && $user !== null) {
                /** @var ConversationMessage $message */
                $message = $paginator[0];
                ConversationHelper::getConversationOpposite(
                    [$message->getConversation()],
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
        ?User $user = null,
        bool $setHydrationMode = false
    ): Query {
        $isSupervisor = $user !== null && WorkRoleHelper::isSupervisor($work, $user);

        $queryPagination = match ($tab) {
            TabTypeConstant::TAB_TASK->value => $this->getTaskQuery($work, $isSupervisor),
            TabTypeConstant::TAB_VERSION->value => $this->getVersionQuery($work),
            TabTypeConstant::TAB_EVENT->value => $this->getEventQuery($work),
            TabTypeConstant::TAB_MESSAGE->value => $this->getMessageQuery($work, $user),
            default => null,
        };

        if ($queryPagination === null) {
            throw new RuntimeException('Query for tab pagination was not created');
        }

        if ($setHydrationMode) {
            switch ($tab) {
                case TabTypeConstant::TAB_TASK->value:
                    $queryPagination->setHydrationMode(Task::class);

                    break;
                case TabTypeConstant::TAB_VERSION->value:
                    $queryPagination->setHydrationMode(Media::class);

                    break;
                case TabTypeConstant::TAB_EVENT->value:
                    $queryPagination->setHydrationMode(Event::class);

                    break;
                case TabTypeConstant::TAB_MESSAGE->value:
                    $queryPagination->setHydrationMode(ConversationMessage::class);

                    break;
            }
        }

        return $queryPagination;
    }

    /**
     * @return array{
     *      pageParameterName: string,
     *      sortFieldParameterName: string,
     *      sortDirectionParameterName: string,
     *      filterFieldParameterName: string,
     *      filterValueParameterName: string,
     *      distinct: bool
     * }
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

    private function getTaskQuery(Work $work, bool $isSupervisor): Query
    {
        /** @var TaskRepository $taskRepository */
        $taskRepository = $this->entityManagerService->getRepository(Task::class);

        return $taskRepository->allByWork($work, !$isSupervisor)->getQuery();
    }

    private function getVersionQuery(Work $work): Query
    {
        /** @var MediaRepository $mediaRepository */
        $mediaRepository = $this->entityManagerService->getRepository(Media::class);

        return $mediaRepository->allByWork($work)->getQuery();
    }

    private function getEventQuery(Work $work): Query
    {
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->entityManagerService->getRepository(Event::class);

        return $eventRepository->allByWork($work)->getQuery();
    }

    private function getMessageQuery(Work $work, ?User $user): Query
    {
        if ($user === null) {
            throw new RuntimeException('User is required for message tab');
        }

        /** @var ConversationMessageRepository $conversationMessageRepository */
        $conversationMessageRepository = $this->entityManagerService->getRepository(ConversationMessage::class);

        return $conversationMessageRepository->allByWorkUser($work, $user)->getQuery();
    }
}
