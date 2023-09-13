<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Tests\Unit\Domain\Task\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\Task\Entity\Task;
use App\Domain\Task\Security\Voter\TaskVoter;
use App\Tests\Helper\Traits\VoterPublicTrait;
use PHPUnit\Framework\TestCase;

class TaskVoterTest extends TestCase
{
    use VoterPublicTrait;

    private readonly TaskVoter $taskVoter;

    protected function setUp(): void
    {
        $this->taskVoter = $this->createVoterPublic(TaskVoter::class);
    }

    public function testSupports(): void
    {
        $task = $this->createMock(Task::class);

        foreach (TaskVoter::SUPPORTS as $support) {
            $this->assertTrue($this->taskVoter->supportsPublic($support, $task));
        }

        $this->assertFalse($this->taskVoter->supportsPublic('invalid_attribute', $task));
        $this->assertFalse($this->taskVoter->supportsPublic(VoterSupportConstant::VIEW->value, 'invalid_subject'));
    }
}
