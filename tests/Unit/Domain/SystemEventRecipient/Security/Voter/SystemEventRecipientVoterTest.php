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

namespace App\Tests\Unit\Domain\SystemEventRecipient\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Domain\SystemEventRecipient\Security\Voter\SystemEventRecipientVoter;
use App\Tests\Helper\Traits\VoterPublicTrait;
use PHPUnit\Framework\TestCase;

class SystemEventRecipientVoterTest extends TestCase
{
    use VoterPublicTrait;

    private readonly SystemEventRecipientVoter $systemEventRecipientVoter;

    protected function setUp(): void
    {
        $this->systemEventRecipientVoter = $this->createVoterPublic(SystemEventRecipientVoter::class);
    }

    public function testSupports(): void
    {
        $systemEventRecipient = $this->createMock(SystemEventRecipient::class);

        $this->assertTrue($this->systemEventRecipientVoter->supportsPublic(
            VoterSupportConstant::CHANGE_VIEWED->value,
            $systemEventRecipient
        ));

        $this->assertFalse($this->systemEventRecipientVoter->supportsPublic('invalid_attribute', $systemEventRecipient));
        $this->assertFalse($this->systemEventRecipientVoter->supportsPublic(VoterSupportConstant::CHANGE_VIEWED->value, 'invalid_subject'));
    }
}
