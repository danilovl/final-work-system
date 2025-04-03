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

namespace App\Tests\Unit\Domain\Version\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\Version\Security\Voter\Subject\VersionVoterSubject;
use App\Domain\Version\Security\Voter\VersionVoter;
use App\Tests\Helper\Application\Security\Voter\TestVoterInterface;
use App\Tests\Helper\Traits\VoterPublicTrait;
use PHPUnit\Framework\TestCase;

class VersionVoterTest extends TestCase
{
    use VoterPublicTrait;

    private TestVoterInterface $versionVoter;

    protected function setUp(): void
    {
        $this->versionVoter = $this->createVoterPublic(VersionVoter::class);
    }

    public function testSupports(): void
    {
        $versionVoterSubject = $this->createMock(VersionVoterSubject::class);

        foreach (VersionVoter::SUPPORTS as $support) {
            $this->assertTrue($this->versionVoter->supportsPublic($support, $versionVoterSubject));
        }

        $this->assertFalse($this->versionVoter->supportsPublic('invalid_attribute', $versionVoterSubject));
        $this->assertFalse($this->versionVoter->supportsPublic(VoterSupportConstant::VIEW->value, 'invalid_subject'));
    }
}
