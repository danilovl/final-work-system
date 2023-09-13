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

namespace App\Domain\Work\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Helper\WorkRoleHelper;
use App\Domain\Work\Service\WorkService;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class WorkVoter extends Voter
{
    public const SUPPORTS = [
        VoterSupportConstant::VIEW->value,
        VoterSupportConstant::EDIT->value,
        VoterSupportConstant::DELETE->value
    ];

    public function __construct(private readonly WorkService $workService) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, self::SUPPORTS, true)) {
            return false;
        }

        if (!$subject instanceof Work) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case VoterSupportConstant::VIEW->value:
                return $this->canView($subject, $user);
            case VoterSupportConstant::EDIT->value:
                return $this->canEdit($subject, $user);
            case VoterSupportConstant::DELETE->value:
                return $this->canDelete($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canView(Work $work, User $user): bool
    {
        return $this->workService->isParticipant($work, $user);
    }

    private function canEdit(Work $work, User $user): bool
    {
        return WorkRoleHelper::isSupervisor($work, $user);
    }

    private function canDelete(Work $work, User $user): bool
    {
        return $this->canEdit($work, $user);
    }
}
