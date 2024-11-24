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

namespace App\Tests\Unit\Domain\EventSchedule\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\EventSchedule\Entity\EventSchedule;
use App\Domain\EventSchedule\Security\Voter\EventScheduleVoter;
use App\Tests\Helper\Traits\VoterPublicTrait;
use PHPUnit\Framework\TestCase;

class EventScheduleVoterTest extends TestCase
{
    use VoterPublicTrait;

    private EventScheduleVoter $eventScheduleVoter;

    protected function setUp(): void
    {
        $this->eventScheduleVoter = $this->createVoterPublic(EventScheduleVoter::class);
    }

    public function testSupports(): void
    {
        $eventSchedule = $this->createMock(EventSchedule::class);

        foreach (EventScheduleVoter::SUPPORTS as $support) {
            $this->assertTrue($this->eventScheduleVoter->supportsPublic($support, $eventSchedule));
        }

        $this->assertFalse($this->eventScheduleVoter->supportsPublic('invalid_attribute', $eventSchedule));
        $this->assertFalse($this->eventScheduleVoter->supportsPublic(VoterSupportConstant::VIEW->value, 'invalid_subject'));
    }
}
