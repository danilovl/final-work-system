<?php declare(strict_types=1);

namespace FinalWork\FinalWorkBundle\Utils\Validator;

use Doctrine\ORM\EntityManagerInterface;
use FinalWork\SonataUserBundle\Entity\User;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class UserValidator
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * AddUserCommand constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }

    /**
     * @param string|null $username
     * @return string
     */
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

    /**
     * @param string|null $username
     * @return string
     */
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

    /**
     * @param string|null $plainPassword
     * @return string
     */
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

    /**
     * @param string|null $email
     * @return string
     */
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

    /**
     * @param string|null $fullName
     * @return string
     */
    public function validateFullName(?string $fullName): string
    {
        if (empty($fullName)) {
            throw new InvalidArgumentException('The full name can not be empty');
        }

        return $fullName;
    }
}
