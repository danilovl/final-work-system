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

use FinalWork\FinalWorkBundle\Constant\{
    MediaTypeConstant,
    VoterSupportConstant
};
use FinalWork\FinalWorkBundle\Entity\Media;
use FinalWork\SonataUserBundle\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DocumentVoter extends Voter
{
    private const SUPPORTS = [
        VoterSupportConstant::EDIT,
        VoterSupportConstant::DOWNLOAD,
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

        if (!$subject instanceof Media) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param Media $subject
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
            case VoterSupportConstant::DOWNLOAD:
                return $this->canDownload($subject);
            case VoterSupportConstant::DELETE:
                return $this->canDelete($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    /**
     * @param Media $media
     * @param User $user
     * @return bool
     */
    private function canEdit(Media $media, User $user): bool
    {
        return $media->isOwner($user);
    }

    /**
     * @param Media $media
     * @return bool
     */
    private function canDownload(Media $media): bool
    {
        return $media->getType()->getId() === MediaTypeConstant::INFORMATION_MATERIAL;
    }

    /**
     * @param Media $media
     * @param User $user
     * @return bool
     */
    private function canDelete(Media $media, User $user): bool
    {
        return $this->canEdit($media, $user);
    }
}