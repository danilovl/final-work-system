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

namespace App\Application\Twig;

use App\Domain\Media\Twig\Runtime\MediaRuntime;
use App\Domain\SystemEvent\Service\SystemEventLinkGeneratorService;
use App\Application\Twig\Runtime\{
    AwayRuntime,
    LocaleRuntime
};
use App\Domain\Conversation\Twig\Runtime\ConversationRuntime;
use App\Domain\Task\Twig\Runtime\TaskRuntime;
use App\Domain\User\Twig\Runtime\UserRuntime;
use App\Domain\Work\Twig\Runtime\WorkRuntime;
use Twig\{
    TwigFilter,
    TwigFunction
};
use Twig\Extension\AbstractExtension;

class TwigExtension extends AbstractExtension
{
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

        $mediaFunctions = [
            new TwigFunction('media_is_file_exist', [MediaRuntime::class, 'isMediaFileExist'])
        ];

        $otherFunctions = [
            new TwigFunction('locales', [LocaleRuntime::class, 'getLocales'])
        ];

        return [
            ...$userFunctions,
            ...$workFunctions,
            ...$taskFunctions,
            ...$conversationFunctions,
            ...$systemEventFunctions,
            ...$mediaFunctions,
            ...$otherFunctions
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
