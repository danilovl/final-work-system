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

namespace App\Security\Voter;

use App\Constant\VoterSupportConstant;
use App\Security\Voter\Subject\VersionVoterSubject;
use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class VersionVoter extends Voter
{
    private const SUPPORTS = [
        VoterSupportConstant::CREATE,
        VoterSupportConstant::VIEW,
        VoterSupportConstant::EDIT,
        VoterSupportConstant::DELETE,
        VoterSupportConstant::DOWNLOAD
    ];

    protected function supports($attribute, $subject): bool
    {
        if (!in_array($attribute, self::SUPPORTS, true)) {
            return false;
        }

        if (!$subject instanceof VersionVoterSubject) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case VoterSupportConstant::CREATE:
                return $this->canCreate($subject, $user);
            case VoterSupportConstant::VIEW:
                return $this->canView($subject);
            case VoterSupportConstant::EDIT:
                return $this->canEdit($subject, $user);
            case VoterSupportConstant::DOWNLOAD:
                return $this->canDownload($subject, $user);
            case VoterSupportConstant::DELETE:
                return $this->canDelete($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canCreate(VersionVoterSubject $versionVoterSubject, User $user): bool
    {
        $work = $versionVoterSubject->getWork();

        return $work->isAuthorSupervisor($user);
    }

    private function canEdit(VersionVoterSubject $versionVoterSubject, User $user): bool
    {
        $work = $versionVoterSubject->getWork();
        $media = $versionVoterSubject->getMedia();

        return $work->isAuthorSupervisor($user) && $media->getWork()->getId() === $work->getId();
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

        return $work->isAuthorSupervisorOpponent($user) && $work->getMedias()->contains($media);
    }

    private function canDelete(VersionVoterSubject $versionVoterSubject, User $user): bool
    {
        return $this->canEdit($versionVoterSubject, $user);
    }
}