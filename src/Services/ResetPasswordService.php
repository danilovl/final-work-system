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

namespace App\Services;

use App\Entity\{
    User,
    ResetPassword
};
use App\Exception\{
    TooManyPasswordRequestsException,
    ExpiredResetPasswordTokenException,
    InvalidResetPasswordTokenException
};
use App\Helper\HashHelper;

use App\Model\ResetPassword\{
    ResetPasswordModel,
    ResetPasswordFacade,
    ResetPasswordFactory,
    ResetPasswordTokenModel
};
use DateTime;
use DateInterval;

class ResetPasswordService
{
    private ResetPasswordFacade $resetPasswordFacade;
    private ResetPasswordFactory $resetPasswordFactory;
    private string $cryptographicallySecureKey;
    private int $resetRequestLifetime;
    private int $requestThrottleTime;

    public function __construct(
        ResetPasswordFactory $resetPasswordFactory,
        ResetPasswordFacade $resetPasswordFacade,
        string $cryptographicallySecureKey,
        int $resetRequestLifetime,
        int $requestThrottleTime
    ) {
        $this->resetPasswordFactory = $resetPasswordFactory;
        $this->resetPasswordFacade = $resetPasswordFacade;
        $this->cryptographicallySecureKey = $cryptographicallySecureKey;
        $this->resetRequestLifetime = $resetRequestLifetime;
        $this->requestThrottleTime = $requestThrottleTime;
    }

    public function getTokenLifetime(): int
    {
        return $this->resetRequestLifetime;
    }

    public function createToken(
        DateTime $expiresAt,
        User $user,
        string $verifier = null
    ): ResetPasswordTokenModel {
        $verifier = $verifier ?? HashHelper::generateDefaultHash();
        $encodedData = json_encode([$verifier, $user->getId(), $expiresAt->getTimestamp()]);

        $resetPasswordModel = new ResetPasswordTokenModel;
        $resetPasswordModel->hashedToken = HashHelper::generateResetPasswordHashedToken($encodedData, $this->cryptographicallySecureKey);

        return $resetPasswordModel;
    }

    public function generateResetToken(User $user): ResetPassword
    {
        if ($availableAt = $this->hasUserHitThrottling($user)) {
            throw new TooManyPasswordRequestsException($availableAt);
        }

        $expiresAt = new DateTime(sprintf('+%d seconds', $this->resetRequestLifetime));

        $resetPasswordTokenModel = $this->createToken($expiresAt, $user);

        $resetPasswordModel = new ResetPasswordModel;
        $resetPasswordModel->user = $user;
        $resetPasswordModel->expiresAt = $expiresAt;
        $resetPasswordModel->hashedToken = $resetPasswordTokenModel->hashedToken;

        return $this->resetPasswordFactory->flushFromModel($resetPasswordModel);
    }

    public function validateTokenAndFetchUser(string $fullToken): User
    {
        $resetRequest = $this->findResetPasswordRequest($fullToken);
        if ($resetRequest === null) {
            throw new InvalidResetPasswordTokenException;
        }

        if ($resetRequest->isExpired()) {
            throw new ExpiredResetPasswordTokenException;
        }

        return $resetRequest->getUser();
    }

    public function removeResetRequest(string $fullToken): void
    {
        $request = $this->findResetPasswordRequest($fullToken);
        if ($request === null) {
            throw new InvalidResetPasswordTokenException();
        }

        $this->resetPasswordFacade->removeResetPassword($request);
    }

    private function findResetPasswordRequest(string $token): ?ResetPassword
    {
        return $this->resetPasswordFacade->findResetPasswordByToken($token);
    }

    private function hasUserHitThrottling(User $user): ?DateTime
    {
        $lastRequestDate = $this->resetPasswordFacade->getMostRecentNonExpiredRequestDate($user);
        if ($lastRequestDate === null) {
            return null;
        }

        $availableAt = (clone $lastRequestDate)->add(new DateInterval("PT{$this->requestThrottleTime}S"));

        return $availableAt > new DateTime ? $availableAt : null;
    }
}
