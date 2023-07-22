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

namespace App\Domain\ResetPassword\Exception;

use DateTime;
use DateTimeInterface;
use Exception;
use Throwable;

final class TooManyPasswordRequestsException extends Exception implements ResetPasswordExceptionInterface
{
    private DateTimeInterface $availableAt;

    public function __construct(
        DateTimeInterface $availableAt,
        string $message = '',
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->availableAt = $availableAt;
    }

    public function getAvailableAt(): DateTimeInterface
    {
        return $this->availableAt;
    }

    public function getRetryAfter(): int
    {
        return $this->getAvailableAt()->getTimestamp() - (new DateTime('now'))->getTimestamp();
    }

    public function getReason(): string
    {
        return 'app.exception.reset_password_too_many_requests';
    }
}
