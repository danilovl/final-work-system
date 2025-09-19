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

namespace App\Domain\WorkCategory\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\User\Entity\User;
use App\Domain\WorkCategory\Entity\WorkCategory;
use LogicException;
use Override;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class WorkCategoryVoter extends Voter
{
    public const array SUPPORTS = [
        VoterSupportConstant::EDIT->value,
        VoterSupportConstant::DELETE->value
    ];

    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof WorkCategory && in_array($attribute, self::SUPPORTS, true);
    }

    #[Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if (!$subject instanceof WorkCategory) {
            return false;
        }

        switch ($attribute) {
            case VoterSupportConstant::EDIT->value:
                return $this->canEdit($subject, $user);
            case VoterSupportConstant::DELETE->value:
                return $this->canDelete($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canEdit(WorkCategory $workCategory, User $user): bool
    {
        return $workCategory->isOwner($user);
    }

    private function canDelete(WorkCategory $workCategory, User $user): bool
    {
        return $this->canEdit($workCategory, $user);
    }
}
