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

namespace FinalWork\SonataUserBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sonata\UserBundle\Entity\BaseGroup;

/**
 * @ORM\Entity(repositoryClass="FinalWork\SonataUserBundle\Entity\Repository\UserGroupRepository")
 * @ORM\Table(name="user_group")
 */
class Group extends BaseGroup
{
    /**
     * @var int $id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="FinalWork\SonataUserBundle\Entity\User", mappedBy="groups")
     */
    protected $users;
}
