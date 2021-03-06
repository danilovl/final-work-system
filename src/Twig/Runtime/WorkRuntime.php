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

namespace App\Twig\Runtime;

use App\Service\WorkService;
use App\Entity\{
    User,
    Work
};
use App\Helper\WorkRoleHelper;
use Twig\Extension\AbstractExtension;

class WorkRuntime extends AbstractExtension
{
    public function __construct(private WorkService $workService)
    {
    }

    public function isWorkRole(
        Work $work,
        User $user,
        string $method
    ): bool {
        return WorkRoleHelper::$method($work, $user);
    }

    public function getDeadlineDays(Work $work): int
    {
        return $this->workService->getDeadlineDays($work);
    }

    public function getDeadlineProgramDays(Work $work): int
    {
        return $this->workService->getDeadlineProgramDays($work);
    }
}

