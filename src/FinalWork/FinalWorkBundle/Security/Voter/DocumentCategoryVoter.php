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

namespace FinalWork\FinalWorkBundle\Security\Voter;

use FinalWork\FinalWorkBundle\Constant\VoterSupportConstant;
use FinalWork\FinalWorkBundle\Entity\MediaCategory;
use FinalWork\SonataUserBundle\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DocumentCategoryVoter extends Voter
{
    private const SUPPORTS = [
        VoterSupportConstant::EDIT,
        VoterSupportConstant::DELETE
    ];

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports($attribute, $subject): bool
    {
        if (!in_array($attribute, self::SUPPORTS, true)) {
            return false;
        }

        if (!$subject instanceof MediaCategory) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param MediaCategory $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case VoterSupportConstant::EDIT:
                return $this->canEdit($subject, $user);
            case VoterSupportConstant::DELETE:
                return $this->canDelete($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    /**
     * @param MediaCategory $mediaCategory
     * @param User $user
     * @return bool
     */
    private function canEdit(MediaCategory $mediaCategory, User $user): bool
    {
        return $mediaCategory->isOwner($user);
    }

    /**
     * @param MediaCategory $mediaCategory
     * @param User $user
     * @return bool
     */
    private function canDelete(MediaCategory $mediaCategory, User $user): bool
    {
        return $this->canEdit($mediaCategory, $user);
    }
}