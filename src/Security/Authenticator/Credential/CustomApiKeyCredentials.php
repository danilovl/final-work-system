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

namespace App\Security\Authenticator\Credential;

use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CredentialsInterface;

class CustomApiKeyCredentials implements CredentialsInterface
{
    public function isResolved(): bool
    {
        return true;
    }
}
