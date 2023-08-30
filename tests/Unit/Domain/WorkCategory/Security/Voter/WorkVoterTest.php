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
use App\Tests\Helper\Traits\VoterPublicTrait;
use PHPUnit\Framework\TestCase;

class WorkVoterTest extends TestCase
{
    use VoterPublicTrait;

    private readonly WorkCategoryVoter $workCategoryVoter;

    protected function setUp(): void
    {
        $this->workCategoryVoter = $this->createVoterPublic(WorkCategoryVoter::class);
    }

    public function testSupports(): void
    {
        $workCategory = $this->createMock(WorkCategory::class);

        $this->assertTrue($this->workCategoryVoter->supportsPublic(VoterSupportConstant::EDIT->value, $workCategory));
        $this->assertTrue($this->workCategoryVoter->supportsPublic(VoterSupportConstant::DELETE->value, $workCategory));

        $this->assertFalse($this->workCategoryVoter->supportsPublic('invalid_attribute', $workCategory));
        $this->assertFalse($this->workCategoryVoter->supportsPublic(VoterSupportConstant::VIEW->value, 'invalid_subject'));
    }
}
