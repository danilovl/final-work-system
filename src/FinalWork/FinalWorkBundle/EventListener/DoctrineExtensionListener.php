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

namespace FinalWork\FinalWorkBundle\EventListener;

use FinalWork\SonataUserBundle\Entity\User;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\{
    ContainerInterface,
    ContainerAwareInterface
};
use Symfony\Component\Security\Core\Security;

class DoctrineExtensionListener implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Security
     */
    private $security;

    /**
     * DoctrineExtensionListener constructor.
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if ($user !== null && $this->container !== null) {
            $loggable = $this->container->get('gedmo.listener.loggable');
            $loggable->setUsername($user->getUsername());
        }
    }
}