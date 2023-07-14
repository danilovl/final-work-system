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

namespace App\Domain\Document\Security\Voter;

use App\Application\Constant\{
    VoterSupportConstant};
use App\Domain\Media\Entity\Media;
use App\Domain\MediaType\Constant\MediaTypeConstant;
use App\Domain\User\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DocumentVoter extends Voter
{
    private const SUPPORTS = [
        VoterSupportConstant::EDIT->value,
        VoterSupportConstant::DOWNLOAD->value,
        VoterSupportConstant::DELETE->value
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
            case VoterSupportConstant::EDIT->value:
                return $this->canEdit($subject, $user);
            case VoterSupportConstant::DOWNLOAD->value:
                return $this->canDownload($subject);
            case VoterSupportConstant::DELETE->value:
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
        return $media->getType()->getId() === MediaTypeConstant::INFORMATION_MATERIAL->value;
    }

    private function canDelete(Media $media, User $user): bool
    {
        return $this->canEdit($media, $user);
    }
}