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

namespace App\Twig;

use App\Twig\Runtime\{
    UserRuntime,
    TaskRuntime,
    WorkRuntime,
    AwayRuntime,
    ConversationRuntime
};
use Twig\{
    TwigFilter,
    TwigFunction
};
use App\Service\SystemEventLinkGeneratorService;
use Twig\Extension\AbstractExtension;

class TwigExtension extends AbstractExtension
{
    public function __construct()
    {
    }

    public function getFunctions(): array
    {
        $userFunctions = [
            new TwigFunction('app_user', [UserRuntime::class, 'appUser']),
            new TwigFunction('is_user_role', [UserRuntime::class, 'isUserRole'])
        ];

        $workFunctions = [
            new TwigFunction('is_work_role', [WorkRuntime::class, 'isWorkRole']),
            new TwigFunction('work_deadline_days', [WorkRuntime::class, 'getDeadlineDays']),
            new TwigFunction('work_deadline_program_days', [WorkRuntime::class, 'getDeadlineProgramDays'])
        ];

        $taskFunctions = [
            new TwigFunction('task_work_complete_percentage', [TaskRuntime::class, 'getCompleteTaskPercentage'])
        ];

        $conversationFunctions = [
            new TwigFunction('check_work_users_conversation', [ConversationRuntime::class, 'checkWorkUsersConversation']),
            new TwigFunction('conversation_last_message', [ConversationRuntime::class, 'getLastMessage']),
            new TwigFunction('conversation_message_read_date_recipient', [ConversationRuntime::class, 'getMessageReadDateByRecipient'])
        ];

        $systemEventFunctions = [
            new TwigFunction('system_event_generate_link', [SystemEventLinkGeneratorService::class, 'generateLink'])
        ];

        return [
            ...$userFunctions,
            ...$workFunctions,
            ...$taskFunctions,
            ...$conversationFunctions,
            ...$systemEventFunctions
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('away_to', [AwayRuntime::class, 'to'], ['is_safe' => ['html']]),
            new TwigFilter('profile_image', [UserRuntime::class, 'profileImage'], ['needs_environment' => true, 'is_safe' => ['html']])
        ];
    }
}
