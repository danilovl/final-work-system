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

namespace App\Domain\Widget\WidgetItem;

use App\Infrastructure\Service\TwigRenderService;
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserService;
use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Domain\SystemEvent\Bus\Query\TotalUnreadSystemEventsByRecipient\{GetTotalUnreadSystemEventsByRecipientQuery, GetTotalUnreadSystemEventsByRecipientQueryResult};
use App\Domain\SystemEventRecipient\Bus\Query\UnreadSystemEventsByRecipient\{GetUnreadSystemEventsByRecipientQuery, GetUnreadSystemEventsByRecipientQueryResult};

class UnreadSystemEventWidget extends BaseWidget
{
    private const int COUNT_VIEW = 6;

    private ?User $user = null;

    public function __construct(
        private readonly TwigRenderService $twigRenderService,
        private readonly UserService $userService,
        private readonly QueryBusInterface $queryBus
    ) {}

    public function getRenderParameters(): array
    {
        $user = $this->user ?? $this->userService->getUser();

        $getTotalUnreadSystemEventsByRecipientQuery = GetTotalUnreadSystemEventsByRecipientQuery::create($user);
        /** @var GetTotalUnreadSystemEventsByRecipientQueryResult $getTotalUnreadSystemEventsByRecipientQueryResult */
        $getTotalUnreadSystemEventsByRecipientQueryResult = $this->queryBus->handle($getTotalUnreadSystemEventsByRecipientQuery);
        $countUnreadSystemEventMessage = $getTotalUnreadSystemEventsByRecipientQueryResult->count;

        $getUnreadSystemEventsByRecipientQuery = GetUnreadSystemEventsByRecipientQuery::create(
            user: $user,
            limit: self::COUNT_VIEW
        );
        /** @var GetUnreadSystemEventsByRecipientQueryResult $getUnreadSystemEventsByRecipientQueryResult */
        $getUnreadSystemEventsByRecipientQueryResult = $this->queryBus->handle($getUnreadSystemEventsByRecipientQuery);
        $systemEventRecipients = $getUnreadSystemEventsByRecipientQueryResult->systemEventRecipients;

        return [
            'countUnreadSystemEventMessage' => $countUnreadSystemEventMessage,
            'systemEventRecipients' => $systemEventRecipients,
        ];
    }

    public function render(): string
    {
        return $this->twigRenderService->render('application/widget/system_event.html.twig', $this->getRenderParameters());
    }

    public function renderForUser(User $user): string
    {
        $this->user = $user;
        $parameters = $this->getRenderParameters();
        $this->user = null;

        return $this->twigRenderService->render('application/widget/system_event.html.twig', $parameters);
    }
}
