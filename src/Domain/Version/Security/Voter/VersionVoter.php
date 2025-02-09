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

namespace App\Domain\Version\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\User\Entity\User;
use App\Domain\Version\Security\Voter\Subject\VersionVoterSubject;
use App\Domain\Work\Helper\WorkRoleHelper;
use LogicException;
use Override;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class VersionVoter extends Voter
{
    public const array SUPPORTS = [
        VoterSupportConstant::CREATE->value,
        VoterSupportConstant::VIEW->value,
        VoterSupportConstant::EDIT->value,
        VoterSupportConstant::DELETE->value,
        VoterSupportConstant::DOWNLOAD->value
    ];

    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, self::SUPPORTS, true)) {
            return false;
        }

        if (!$subject instanceof VersionVoterSubject) {
            return false;
        }

        return true;
    }

    #[Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if (!$subject instanceof VersionVoterSubject) {
            return false;
        }

        switch ($attribute) {
            case VoterSupportConstant::CREATE->value:
                return $this->canCreate($subject, $user);
            case VoterSupportConstant::VIEW->value:
                return $this->canView($subject);
            case VoterSupportConstant::EDIT->value:
                return $this->canEdit($subject, $user);
            case VoterSupportConstant::DOWNLOAD->value:
                return $this->canDownload($subject, $user);
            case VoterSupportConstant::DELETE->value:
                return $this->canDelete($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canCreate(VersionVoterSubject $versionVoterSubject, User $user): bool
    {
        $work = $versionVoterSubject->getWork();

        return WorkRoleHelper::isAuthorSupervisor($work, $user);
    }

    private function canEdit(VersionVoterSubject $versionVoterSubject, User $user): bool
    {
        $work = $versionVoterSubject->getWork();
        $media = $versionVoterSubject->getMedia();

        return WorkRoleHelper::isAuthorSupervisor($work, $user) && $media->getWorkMust()->getId() === $work->getId();
    }

    private function canView(VersionVoterSubject $versionVoterSubject): bool
    {
        $media = $versionVoterSubject->getMedia();

        return $media->getWork() !== null;
    }

    private function canDownload(VersionVoterSubject $versionVoterSubject, User $user): bool
    {
        $work = $versionVoterSubject->getWork();
        $media = $versionVoterSubject->getMedia();

        return WorkRoleHelper::hasAccessToWork($work, $user) && $work->getMedias()->contains($media);
    }

    private function canDelete(VersionVoterSubject $versionVoterSubject, User $user): bool
    {
        return $this->canEdit($versionVoterSubject, $user);
    }
}
