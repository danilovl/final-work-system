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

namespace App\Domain\User\Model;

use App\Domain\User\Entity\User;
use Doctrine\Common\Collections\Collection;

class UserModel
{
    public ?int $id = null;

    public ?string $degreeBefore = null;

    public string $firstName;

    public string $lastName;

    public ?string $degreeAfter = null;

    public ?string $phone = null;

    public ?string $skype = null;

    public string $email;

    public string $emailCanonical;

    public string $username;

    public ?string $usernameCanonical = null;

    public ?string $salt = null;

    public string $password;

    public ?string $plainPassword = null;

    public ?string $messageGreeting = null;

    public ?string $messageSignature = null;

    public ?string $locale = null;

    public ?string $role = null;

    public array $roles = [];

    public bool $enable = false;

    public bool $enabledEmailNotification = true;

    public Collection $groups;

    public static function fromUser(User $user): self
    {
        $model = new self;
        $model->id = $user->getId();
        $model->degreeBefore = $user->getDegreeBefore();
        $model->firstName = $user->getFirstname();
        $model->lastName = $user->getLastname();
        $model->degreeAfter = $user->getDegreeAfter();
        $model->phone = $user->getPhone();
        $model->skype = $user->getSkype();
        $model->email = $user->getEmail();
        $model->emailCanonical = $user->getEmailCanonical();
        $model->salt = $user->getSalt();
        $model->password = $user->getPassword();
        $model->plainPassword = $user->getPlainPassword();
        $model->username = $user->getUsername();
        $model->usernameCanonical = $user->getUsernameCanonical();
        $model->messageGreeting = $user->getMessageGreeting();
        $model->messageSignature = $user->getMessageSignature();
        $model->locale = $user->getLocale();
        $model->roles = $user->getRoles();
        $model->groups = $user->getGroups();
        $model->enable = $user->isEnabled();
        $model->enabledEmailNotification = $user->isEnabledEmailNotification();

        return $model;
    }
}
