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

namespace App\Tests\Unit\Domain\EventAddress\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\EventAddress\Entity\EventAddress;
use App\Domain\EventAddress\Security\Voter\EventAddressVoter;
use App\Tests\Mock\Application\Security\Voter\VoterInterfaceMock;
use App\Tests\Mock\Application\Traits\VoterPublicTraitMock;
use PHPUnit\Framework\TestCase;

class EventAddressVoterTest extends TestCase
{
    use VoterPublicTraitMock;

    private VoterInterfaceMock $eventAddressVoter;

    protected function setUp(): void
    {
        $this->eventAddressVoter = $this->createVoterPublic(EventAddressVoter::class);
    }

    public function testSupports(): void
    {
        $eventAddress = $this->createStub(EventAddress::class);

        foreach (EventAddressVoter::SUPPORTS as $support) {
            $this->assertTrue($this->eventAddressVoter->supportsPublic($support, $eventAddress));
        }

        $this->assertFalse($this->eventAddressVoter->supportsPublic('invalid_attribute', $eventAddress));
        $this->assertFalse($this->eventAddressVoter->supportsPublic(VoterSupportConstant::VIEW->value, 'invalid_subject'));
    }
}
