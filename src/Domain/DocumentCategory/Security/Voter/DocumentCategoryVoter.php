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

namespace App\Domain\DocumentCategory\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\MediaCategory\Entity\MediaCategory;
use App\Domain\User\Entity\User;
use LogicException;
use Override;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\{
    Vote,
    Voter
};

class DocumentCategoryVoter extends Voter
{
    public const array SUPPORTS = [
        VoterSupportConstant::EDIT->value,
        VoterSupportConstant::DELETE->value
    ];

    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof MediaCategory && in_array($attribute, self::SUPPORTS, true);
    }

    #[Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if (!$subject instanceof MediaCategory) {
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

    private function canEdit(MediaCategory $mediaCategory, User $user): bool
    {
        return $mediaCategory->isOwner($user);
    }

    private function canDelete(MediaCategory $mediaCategory, User $user): bool
    {
        return $this->canEdit($mediaCategory, $user);
    }
}
