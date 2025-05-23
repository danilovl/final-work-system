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

namespace App\Tests\Mock\Application\Traits;

use App\Domain\User\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface;

trait SecurityLoginTraitMock
{
    public function loginUser(KernelInterface $kernel, User $user): void
    {
        $request = new Request;
        $request->setSession(new Session);
        $kernel->getContainer()->get('request_stack')->push($request);

        $security = $kernel->getContainer()->get(Security::class);
        $security->login($user);
    }

    public function logicAnonUser(KernelInterface $kernel): void
    {
        $user = new User;
        $user->setId(1);
        $user->setEmail('test@example.com');
        $user->setFirstname('test');
        $user->setLastname('test');

        $this->loginUser($kernel, $user);
    }
}
