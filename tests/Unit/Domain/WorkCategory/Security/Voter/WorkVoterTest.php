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

namespace App\Tests\Unit\Domain\WorkCategory\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\WorkCategory\Entity\WorkCategory;
use App\Domain\WorkCategory\Security\Voter\WorkCategoryVoter;
use App\Tests\Mock\Application\Security\Voter\VoterInterfaceMock;
use App\Tests\Mock\Application\Traits\VoterPublicTraitMock;
use PHPUnit\Framework\TestCase;

class WorkVoterTest extends TestCase
{
    use VoterPublicTraitMock;

    private VoterInterfaceMock $workCategoryVoter;

    protected function setUp(): void
    {
        $this->workCategoryVoter = $this->createVoterPublic(WorkCategoryVoter::class);
    }

    public function testSupports(): void
    {
        $workCategory = $this->createMock(WorkCategory::class);

        foreach (WorkCategoryVoter::SUPPORTS as $support) {
            $this->assertTrue($this->workCategoryVoter->supportsPublic($support, $workCategory));
        }

        $this->assertFalse($this->workCategoryVoter->supportsPublic('invalid_attribute', $workCategory));
        $this->assertFalse($this->workCategoryVoter->supportsPublic(VoterSupportConstant::VIEW->value, 'invalid_subject'));
    }
}
