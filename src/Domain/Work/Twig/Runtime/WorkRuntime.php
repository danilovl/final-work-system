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

namespace App\Domain\Work\Twig\Runtime;

use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Helper\WorkRoleHelper;
use App\Domain\Work\Service\WorkService;
use Danilovl\RenderServiceTwigExtensionBundle\Attribute\AsTwigFunction;

class WorkRuntime
{
    public function __construct(private readonly WorkService $workService) {}

    #[AsTwigFunction('is_work_role')]
    public function isWorkRole(
        Work $work,
        User $user,
        string $method
    ): bool {
        return WorkRoleHelper::$method($work, $user);
    }

    #[AsTwigFunction('work_deadline_days')]
    public function getDeadlineDays(Work $work): int
    {
        return $this->workService->getDeadlineDays($work);
    }

    #[AsTwigFunction('work_deadline_program_days')]
    public function getDeadlineProgramDays(Work $work): int
    {
        return $this->workService->getDeadlineProgramDays($work);
    }
}
