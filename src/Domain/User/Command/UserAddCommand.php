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

namespace App\Domain\User\Command;

use App\Application\Helper\HashHelper;
use App\Infrastructure\Service\EntityManagerService;
use App\Domain\User\Command\Validator\UserValidator;
use App\Domain\User\Entity\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Stopwatch\Stopwatch;

#[AsCommand(name: 'app:user-add', description: 'Creates users and stores them in the database')]
class UserAddCommand
{
    final public const string COMMAND_NAME = 'app:user-add';

    public function __construct(
        private readonly EntityManagerService $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly UserValidator $validator
    ) {}

    public function __invoke(
        SymfonyStyle $io,
        OutputInterface $output,
        #[InputArgument(
            name: 'username',
            mode: InputArgument::OPTIONAL,
            description: 'The username of the new user'
        )]
        ?string $username = null,

        #[InputArgument(
            name: 'password',
            mode: InputArgument::OPTIONAL,
            description: 'The plain password of the new user'
        )]
        ?string $password = null,

        #[InputArgument(
            name: 'email',
            mode: InputArgument::OPTIONAL,
            description: 'The email of the new user'
        )]
        ?string $email = null,

        #[InputArgument(
            name: 'first-name',
            mode: InputArgument::OPTIONAL,
            description: 'The first name of the new user'
        )]
        ?string $firstName = null,

        #[InputArgument(
            name: 'last-name',
            mode: InputArgument::OPTIONAL,
            description: 'The last name of the new user'
        )]
        ?string $lastName = null,

        #[InputArgument(
            name: 'roles',
            mode: InputArgument::OPTIONAL,
            description: 'Roles'
        )]
        ?string $roles = null
    ): int {
        if (!$this->validInteractInputArguments($username, $password, $email, $firstName, $lastName, $roles)) {
            $io->title('Add user command');

            if ($username !== null) {
                $io->text(' > <info>Username</info>: ' . $username);
            } else {
                $username = $io->ask('Username', null, [$this->validator, 'validateUsername']);
            }

            if ($email !== null) {
                $io->text(' > <info>Email</info>: ' . $email);
            } else {
                $email = $io->ask('Email', null, [$this->validator, 'validateEmail']);
            }

            if ($password !== null) {
                $io->text(' > <info>Password</info>: ' . str_repeat('*', mb_strlen($password)));
            } else {
                $password = $io->askHidden('Password (your type will be hidden)', [$this->validator, 'validatePassword']);
            }

            if ($firstName !== null) {
                $io->text(' > <info>First Name</info>: ' . $firstName);
            } else {
                $firstName = $io->ask('First Name', null, [$this->validator, 'validateFullName']);
            }

            if ($lastName !== null) {
                $io->text(' > <info>Last Name</info>: ' . $lastName);
            } else {
                $lastName = $io->ask('Last Name', null, [$this->validator, 'validateFullName']);
            }

            if ($roles !== null) {
                $io->text(' > <info>Roles(ROLE_USER,ROLE_ADMIN)</info>: ' . $roles);
            } else {
                $roles = $io->ask('Roles', null, [$this->validator, 'validateRoles']);
            }
        }

        $stopwatch = new Stopwatch;
        $stopwatch->start('add-user-command');

        $this->validateUserData($username, $password, $email, $firstName, $lastName);

        $user = new User;
        $user->setEnabled(true);
        $user->setFirstname($firstName);
        $user->setLastName($lastName);
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setEmailCanonical($email);
        $user->setRoles(explode(',', $roles));
        $user->setSalt(HashHelper::generateUserSalt());

        $encodedPassword = $this->userPasswordHasher->hashPassword($user, $password);
        $user->setPassword($encodedPassword);

        $this->entityManager->persistAndFlush($user);

        $io->success(sprintf('User was successfully created: %s (%s)', $user->getUsername(), $user->getEmail()));

        $event = $stopwatch->stop('add-user-command');
        if ($output->isVerbose()) {
            $io->comment(
                sprintf('New user database id: %d / Elapsed time: %.2f ms / Consumed memory: %.2f MB',
                    $user->getId(),
                    $event->getDuration(),
                    $event->getMemory() / (1_024 ** 2)
                )
            );
        }

        return Command::SUCCESS;
    }

    private function validateUserData(
        string $username,
        string $plainPassword,
        string $email,
        string $firstName,
        string $lastName
    ): void {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => $username]);

        if ($user !== null) {
            throw new RuntimeException(sprintf('There is already a user registered with the "%s" username.', $username));
        }

        $this->validator->validateUsername($username);
        $this->validator->validatePassword($plainPassword);
        $this->validator->validateEmail($email);
        $this->validator->validateFullName($firstName);
        $this->validator->validateFullName($lastName);
    }

    private function validInteractInputArguments(
        ?string $username,
        ?string $password,
        ?string $email,
        ?string $firstName,
        ?string $lastName,
        ?string $roles
    ): bool {
        return $username !== null && 
               $password !== null && 
               $email !== null && 
               $firstName !== null && 
               $lastName !== null && 
               $roles !== null;
    }
}
