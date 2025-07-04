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

namespace App\Tests\Unit\Domain\EventParticipant\Helper;

use App\Domain\EventParticipant\Entity\EventParticipant;
use App\Domain\EventParticipant\Helper\SortFunctionHelper;
use App\Domain\User\Entity\User;
use PHPUnit\Framework\TestCase;

class SortFunctionHelperTest extends TestCase
{
    public function testEventParticipantSort(): void
    {
        $user1 = new User;
        $user1->setFirstname('žbaňka');
        $user1->setLastname('');
        $participant1 = new EventParticipant;
        $participant1->setUser($user1);

        $user2 = new User;
        $user2->setFirstname('ářam');
        $user2->setLastname('');
        $participant2 = new EventParticipant;
        $participant2->setUser($user2);

        $user3 = new User;
        $user3->setFirstname('čary');
        $user3->setLastname('');
        $participant3 = new EventParticipant;
        $participant3->setUser($user3);

        $participants = [$participant1, $participant2, $participant3];
        SortFunctionHelper::eventParticipantSort($participants);

        $this->assertEquals($participant2, $participants[0]);
        $this->assertEquals($participant3, $participants[1]);
        $this->assertEquals($participant1, $participants[2]);
    }
}
