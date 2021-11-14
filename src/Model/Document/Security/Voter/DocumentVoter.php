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

namespace App\Model\Document\Security\Voter;

use App\Constant\{
    MediaTypeConstant,
    VoterSupportConstant
};
use App\Entity\{
    User,
    Media
};
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

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, self::SUPPORTS, true)) {
            return false;
        }

        if (!$subject instanceof Media) {
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
            case VoterSupportConstant::EDIT:
                return $this->canEdit($subject, $user);
            case VoterSupportConstant::DOWNLOAD:
                return $this->canDownload($subject);
            case VoterSupportConstant::DELETE:
                return $this->canDelete($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canEdit(Media $media, User $user): bool
    {
        return $media->isOwner($user);
    }

    private function canDownload(Media $media): bool
    {
        return $media->getType()->getId() === MediaTypeConstant::INFORMATION_MATERIAL;
    }

    private function canDelete(Media $media, User $user): bool
    {
        return $this->canEdit($media, $user);
    }
}