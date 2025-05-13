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

namespace App\Domain\ResetPassword\Facade;

use App\Domain\ResetPassword\Entity\ResetPassword;
use App\Domain\ResetPassword\Repository\ResetPasswordRepository;
use App\Domain\User\Entity\User;
use DateTime;

readonly class ResetPasswordFacade
{
    public function __construct(private ResetPasswordRepository $resetPasswordRepository) {}

    public function find(int $id): ?ResetPassword
    {
        /** @var ResetPassword|null $result */
        $result = $this->resetPasswordRepository->find($id);

        return $result;
    }

    public function findResetPasswordByToken(string $token): ?ResetPassword
    {
        /** @var ResetPassword|null $result */
        $result = $this->resetPasswordRepository
            ->byToken($token)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    public function removeResetPassword(ResetPassword $resetPassword): void
    {
        $this->resetPasswordRepository
            ->baseQueryBuilder()
            ->delete()
            ->where('reset_password.user = :user')
            ->setParameter('user', $resetPassword->getUser())
            ->getQuery()
            ->execute();
    }

    public function getMostRecentNonExpiredRequestDate(User $user): ?DateTime
    {
        /** @var ResetPassword|null $resetPassword */
        $resetPassword = $this->resetPasswordRepository
            ->mostRecentNonExpiredRequestDate($user)
            ->getQuery()
            ->getOneorNullResult();

        if ($resetPassword !== null && !$resetPassword->isExpired()) {
            return  DateTime::createFromImmutable($resetPassword->getCreatedAt());
        }

        return null;
    }
}
