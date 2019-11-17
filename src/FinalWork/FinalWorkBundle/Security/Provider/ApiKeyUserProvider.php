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

namespace FinalWork\FinalWorkBundle\Security\Provider;

use Doctrine\ORM\NonUniqueResultException;
use FinalWork\FinalWorkBundle\Entity\ApiUser;
use FinalWork\FinalWorkBundle\Model\ApiUser\ApiUserFacade;
use Symfony\Component\Security\Core\Exception\{
    UnsupportedUserException,
    UsernameNotFoundException
};
use Symfony\Component\Security\Core\User\{
    User,
    UserInterface,
    UserProviderInterface
};

class ApiKeyUserProvider implements UserProviderInterface
{
    /**
     * @var ApiUserFacade
     */
    private $apiUserFacade;

    /**
     * ApiKeyUserProvider constructor.
     * @param ApiUserFacade $apiUserFacade
     */
    public function __construct(ApiUserFacade $apiUserFacade)
    {
        $this->apiUserFacade = $apiUserFacade;
    }

    /**
     * @param string $username
     * @return User|UserInterface
     * @throws NonUniqueResultException
     */
    public function loadUserByUsername($username): UserInterface
    {
        /** @var ApiUser|null $user */
        $user = $this->apiUserFacade->findByApiKey($username);
        if ($user === null) {
            throw new UsernameNotFoundException();
        }

        return $user;
    }

    /**
     * @param UserInterface $user
     * @return UserInterface|void
     */
    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class): bool
    {
        return ApiUser::class === $class;
    }
}