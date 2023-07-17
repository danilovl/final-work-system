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

namespace App\Domain\User;

use Doctrine\Common\Collections\Collection;
use App\Domain\User\Entity\User;

class UserModel
{
    public ?string $degreeBefore = null;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $degreeAfter = null;
    public ?string $phone = null;
    public ?string $skype = null;
    public ?string $email = null;
    public ?string $emailCanonical = null;
    public ?string $username = null;
    public ?string $usernameCanonical = null;
    public ?string $salt = null;
    public ?string $password = null;
    public ?string $plainPassword = null;
    public ?string $messageGreeting = null;
    public ?string $messageSignature = null;
    public ?string $locale = null;
    public ?string $role = null;
    public ?array $roles = null;
    public bool $enable = false;
    public bool $enabledEmailNotification = true;
    public ?Collection $groups = null;

    public static function fromUser(User $user): self
    {
        $model = new self;
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
