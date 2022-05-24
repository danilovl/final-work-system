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

use App\Application\Helper\WorkRoleHelper;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Service\WorkService;
use Twig\Extension\AbstractExtension;
use Twig\Extension\RuntimeExtensionInterface;

class WorkRuntime extends AbstractExtension implements RuntimeExtensionInterface
{
    public function __construct(private readonly WorkService $workService)
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

