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

namespace App\Tests\Unit\Application\Security\Authenticator\Credential;

use App\Application\Security\Authenticator\Credential\CustomApiKeyCredentials;
use PHPUnit\Framework\TestCase;

class CustomApiKeyCredentialsTest extends TestCase
{
    public function testIsResolved(): void
    {
        $customApiKeyCredentials = new CustomApiKeyCredentials;

        $this->assertTrue($customApiKeyCredentials->isResolved());
    }
}
