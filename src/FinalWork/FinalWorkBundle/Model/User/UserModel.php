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

namespace FinalWork\FinalWorkBundle\Model\User;

use FinalWork\SonataUserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class UserModel
{
    /**
     * @var string|null
     */
    public $degreeBefore;

    /**
     * @var string|null
     * @Assert\NotBlank()
     */
    public $firstName;

    /**
     * @var string|null
     * @Assert\NotBlank()
     */
    public $lastName;

    /**
     * @var string|null
     */
    public $degreeAfter;

    /**
     * @var string|null
     */
    public $phone;

    /**
     * @var string|null
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    public $email;

    /**
     * @var string|null
     * @Assert\NotBlank()
     */
    public $username;

    /**
     * @var string|null
     */
    public $role;

    /**
     * @var array|null
     */
    public $roles;

    /**
     * @var string|null
     */
    public $groups;

    /**
     * @param User $user
     * @return UserModel
     */
    public static function fromUser(User $user): self
    {
        $model = new self();
        $model->degreeBefore = $user->getDegreeBefore();
        $model->firstName = $user->getFirstname();
        $model->lastName = $user->getLastname();
        $model->degreeAfter = $user->getDegreeAfter();
        $model->phone = $user->getPhone();
        $model->email = $user->getEmail();
        $model->username = $user->getUsername();
        $model->roles = $user->getRoles();
        $model->groups = $user->getGroups();

        return $model;
    }
}
