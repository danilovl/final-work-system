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

namespace App\Tests\Unit\Domain\Work\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Security\Voter\WorkVoter;
use App\Domain\Work\Service\WorkService;
use PHPUnit\Framework\TestCase;

class WorkVoterTest extends TestCase
{
    private WorkVoter $workVoter;

    protected function setUp(): void
    {
        $this->workVoter = new class(new WorkService) extends WorkVoter {
            public function supportsPublic(string $attribute, mixed $subject): bool
            {
                return $this->supports($attribute, $subject);
            }
        };
    }

    public function testSupports(): void
    {
        $work = $this->createMock(Work::class);

        foreach (WorkVoter::SUPPORTS as $support) {
            $this->assertTrue($this->workVoter->supportsPublic($support, $work));
        }

        $this->assertFalse($this->workVoter->supportsPublic('invalid_attribute', $work));
        $this->assertFalse($this->workVoter->supportsPublic(VoterSupportConstant::VIEW->value, 'invalid_subject'));
    }
}
