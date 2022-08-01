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

namespace App\Application\Widget;

use App\Application\Service\UserService;
use App\Domain\SystemEvent\Facade\SystemEventRecipientFacade;
use App\Domain\SystemEvent\Facade\{
    SystemEventFacade
};
use Twig\Environment;

class UnreadSystemEventWidget extends BaseWidget
{
    private const COUNT_VIEW = 6;

    public function __construct(
        private readonly Environment $environment,
        private readonly UserService $userService,
        private readonly SystemEventFacade $systemEventFacade,
        private readonly SystemEventRecipientFacade $systemEventRecipientFacade
    ) {}

    public function getRenderParameters(): array
    {
        $user = $this->userService->getUser();
        $countUnreadSystemEventMessage = $this->systemEventFacade
            ->getTotalUnreadSystemEventsByRecipient($user);

        $systemEventRecipients = $this->systemEventRecipientFacade
            ->getUnreadSystemEventsByRecipient($user, self::COUNT_VIEW);

        return [
            'countUnreadSystemEventMessage' => $countUnreadSystemEventMessage,
            'systemEventRecipients' => $systemEventRecipients,
        ];
    }

    public function render(): string
    {
        return $this->environment->render('widget/system_event.html.twig', $this->getRenderParameters());
    }
}
