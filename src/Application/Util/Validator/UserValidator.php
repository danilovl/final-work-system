<?php declare(strict_types=1);

namespace App\Application\Util\Validator;

use App\Domain\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Exception\InvalidArgumentException;

readonly class UserValidator
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function validateUsername(?string $username): string
    {
        if (empty($username)) {
            throw new InvalidArgumentException('The username can not be empty');
        }

        if (preg_match('/^[a-z_0-9]+$/', $username) !== 1) {
            throw new InvalidArgumentException('The username must contain only lowercase latin characters and underscores');
        }

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => $username]);

        if ($user !== null) {
            throw new InvalidArgumentException('The username is already used');
        }

        return $username;
    }

    public function validateUsernameExist(?string $username): string
    {
        if (empty($username)) {
            throw new InvalidArgumentException('The username can not be empty');
        }

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => $username]);

        if ($user === null) {
            throw new InvalidArgumentException('The username is not exist');
        }

        return $username;
    }

    public function validatePassword(?string $plainPassword): string
    {
        if (empty($plainPassword)) {
            throw new InvalidArgumentException('The password can not be empty');
        }

        if (mb_strlen(trim($plainPassword)) < 6) {
            throw new InvalidArgumentException('The password must be at least 6 characters long');
        }

        return $plainPassword;
    }

    public function validateEmail(?string $email): string
    {
        if (empty($email)) {
            throw new InvalidArgumentException('The email can not be empty');
        }

        if (mb_strpos($email, '@') === false) {
            throw new InvalidArgumentException('The email should look like a real email');
        }

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if ($user !== null) {
            throw new InvalidArgumentException('The email is already used');
        }

        return $email;
    }

    public function validateFullName(?string $fullName): string
    {
        if (empty($fullName)) {
            throw new InvalidArgumentException('The full name can not be empty');
        }

        return $fullName;
    }

    public function validateRoles(?string $roles): string
    {
        if (empty($roles)) {
            throw new InvalidArgumentException('The roles can not be empty');
        }

        return $roles;
    }
}
