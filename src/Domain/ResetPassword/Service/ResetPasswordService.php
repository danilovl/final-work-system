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

namespace App\Domain\ResetPassword\Service;

use App\Application\Helper\HashHelper;
use App\Domain\ResetPassword\Entity\ResetPassword;
use DateTimeImmutable;
use App\Domain\ResetPassword\Exception\{
    TooManyPasswordRequestsException,
    ExpiredResetPasswordTokenException,
    InvalidResetPasswordTokenException
};
use App\Domain\ResetPassword\Facade\ResetPasswordFacade;
use App\Domain\ResetPassword\Factory\ResetPasswordFactory;
use App\Domain\ResetPassword\Model\{
    ResetPasswordModel,
    ResetPasswordTokenModel
};
use App\Domain\User\Entity\User;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use DateInterval;
use DateTime;

readonly class ResetPasswordService
{
    private string $cryptographicallySecureKey;

    private int $resetRequestLifetime;

    private int $requestThrottleTime;

    public function __construct(
        private ResetPasswordFactory $resetPasswordFactory,
        private ResetPasswordFacade $resetPasswordFacade,
        ParameterServiceInterface $parameterService
    ) {
        $this->cryptographicallySecureKey = $parameterService->getString('reset_password.cryptographically_secure_key');
        $this->resetRequestLifetime = $parameterService->getInt('reset_password.reset_request_lifetime');
        $this->requestThrottleTime = $parameterService->getInt('reset_password.request_throttle_time');
    }

    public function getTokenLifetime(): int
    {
        return $this->resetRequestLifetime;
    }

    public function createToken(
        DateTime $expiresAt,
        User $user,
        ?string $verifier = null
    ): ResetPasswordTokenModel {
        $verifier ??= HashHelper::generateDefaultHash();
        /** @var string $encodedData */
        $encodedData = json_encode([$verifier, $user->getId(), $expiresAt->getTimestamp()]);

        $resetPasswordTokenModel = new ResetPasswordTokenModel;
        $resetPasswordTokenModel->hashedToken = HashHelper::generateResetPasswordHashedToken($encodedData, $this->cryptographicallySecureKey);

        return $resetPasswordTokenModel;
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
            throw new InvalidResetPasswordTokenException;
        }

        $this->resetPasswordFacade->remove($request);
    }

    private function findResetPasswordRequest(string $token): ?ResetPassword
    {
        return $this->resetPasswordFacade->findByToken($token);
    }

    private function hasUserHitThrottling(User $user): ?DateTime
    {
        $lastRequestDate = $this->resetPasswordFacade->findMostRecentNonExpiredRequestDate($user);
        if ($lastRequestDate === null) {
            return null;
        }

        $availableAt = (clone $lastRequestDate)->add(new DateInterval("PT{$this->requestThrottleTime}S"));

        return $availableAt > new DateTimeImmutable ? $availableAt : null;
    }
}
