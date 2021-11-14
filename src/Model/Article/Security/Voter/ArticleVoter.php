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

namespace App\Model\Article\Security\Voter;

use App\Constant\VoterSupportConstant;
use App\Helper\FunctionHelper;
use App\Entity\User;
use App\Model\Article\Security\Voter\Subject\ArticleVoterSubject;
use LogicException;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ArticleVoter extends Voter
{
    private const SUPPORTS = [
        VoterSupportConstant::VIEW
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, self::SUPPORTS, true)) {
            return false;
        }

        if (!$subject instanceof ArticleVoterSubject) {
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

        if ($attribute === VoterSupportConstant::VIEW) {
            return $this->canView($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canView(ArticleVoterSubject $articleVoterSubject, User $user): bool
    {
        $article = $articleVoterSubject->getArticle();
        $articleCategory = $articleVoterSubject->getArticleCategory();

        return $article->isActive() &&
            FunctionHelper::checkIntersectTwoArray($user->getRoles(), $articleCategory->getAccess()) &&
            $article->getCategories()->contains($articleCategory);
    }
}