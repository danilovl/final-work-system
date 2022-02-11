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

namespace App\Domain\Work\Service;

use App\Domain\Work\Entity\Work;
use DateTime;
use App\Domain\User\Entity\User;

class WorkService
{
    public function isParticipant(
        Work $work,
        User $user
    ): bool {
        foreach ($this->getAllUsers($work) as $participant) {
            if ($participant->getId() === $user->getId()) {
                return true;
            }
        }

        return false;
    }

    public function getAllUsers(Work $work): array
    {
        return $this->getUsers($work, true, true, true, true);
    }

    public function getUsers(
        Work $work,
        bool $author = null,
        bool $supervisor = null,
        bool $consultant = null,
        bool $opponent = null
    ): array {
        $users = [];

        if ($author === true && $work->getAuthor() !== null) {
            $users[] = $work->getAuthor();
        }

        if ($opponent === true && $work->getOpponent() !== null) {
            $users[] = $work->getOpponent();
        }

        if ($supervisor === true && $work->getSupervisor() !== null) {
            $users[] = $work->getSupervisor();
        }

        if ($consultant === true && $work->getConsultant() !== null) {
            $users[] = $work->getConsultant();
        }

        return $users;
    }

    public function getDeadlineDays(Work $work): int
    {
        $now = new DateTime;
        $diff = (int) $work->getDeadline()->diff($now)->format('%a');

        return $work->getDeadline()->diff($now)->invert ? $diff : -$diff;
    }

    public function getDeadlineProgramDays(Work $work): int
    {
        $now = new DateTime;
        $d = $now->diff($work->getDeadlineProgram())->d;

        return $now->diff($work->getDeadline())->invert ? -$d : $d;
    }
}
