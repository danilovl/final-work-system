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

namespace App\Tests\Unit\Domain\Event\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Security\Voter\EventVoter;
use App\Tests\Helper\Traits\VoterPublicTrait;
use PHPUnit\Framework\TestCase;

class EventAddressVoterTest extends TestCase
{
    use VoterPublicTrait;

    private EventVoter $eventVoter;

    protected function setUp(): void
    {
        $this->eventVoter = $this->createVoterPublic(EventVoter::class);
    }

    public function testSupports(): void
    {
        $event = $this->createMock(Event::class);

        foreach (EventVoter::SUPPORTS as $support) {
            $this->assertTrue($this->eventVoter->supportsPublic($support, $event));
        }

        $this->assertFalse($this->eventVoter->supportsPublic('invalid_attribute', $event));
        $this->assertFalse($this->eventVoter->supportsPublic(VoterSupportConstant::VIEW->value, 'invalid_subject'));
    }
}
