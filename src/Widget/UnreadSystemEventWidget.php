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

namespace App\Widget;

use App\Model\SystemEvent\{
    SystemEventFacade,
    SystemEventRecipientFacade
};
use App\Service\UserService;
use Twig\Environment;

class UnreadSystemEventWidget extends BaseWidget
{
    private const COUNT_VIEW = 6;

    public function __construct(
        private Environment $environment,
        private UserService $userService,
        private SystemEventFacade $systemEventFacade,
        private SystemEventRecipientFacade $systemEventRecipientFacade
    ) {
    }

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
