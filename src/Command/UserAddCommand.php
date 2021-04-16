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

namespace App\Command;

use App\Helper\HashHelper;
use App\Service\EntityManagerService;
use App\Util\Validator\UserValidator;
use App\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\{
    InputArgument,
    InputInterface
};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class UserAddCommand extends Command
{
    protected static $defaultName = 'app:user-add';

    private ?SymfonyStyle $io = null;

    public function __construct(
        private EntityManagerService $entityManager,
        private UserPasswordEncoderInterface $passwordEncoder,
        private UserValidator $validator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Creates users and stores them in the database')
            ->addArgument('username', InputArgument::OPTIONAL, 'The username of the new user')
            ->addArgument('password', InputArgument::OPTIONAL, 'The plain password of the new user')
            ->addArgument('email', InputArgument::OPTIONAL, 'The email of the new user')
            ->addArgument('first-name', InputArgument::OPTIONAL, 'The first name of the new user')
            ->addArgument('last-name', InputArgument::OPTIONAL, 'The last name of the new user')
            ->addArgument('roles', InputArgument::OPTIONAL, 'Roles');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if ($this->validInteractInputArguments($input)) {
            return;
        }

        $this->io->title('Add user command');

        $username = $input->getArgument('username');
        if ($username !== null) {
            $this->io->text(' > <info>Username</info>: ' . $username);
        } else {
            $username = $this->io->ask('Username', null, [$this->validator, 'validateUsername']);
            $input->setArgument('username', $username);
        }

        $email = $input->getArgument('email');
        if ($email !== null) {
            $this->io->text(' > <info>Email</info>: ' . $email);
        } else {
            $email = $this->io->ask('Email', null, [$this->validator, 'validateEmail']);
            $input->setArgument('email', $email);
        }

        $password = $input->getArgument('password');
        if ($password !== null) {
            $this->io->text(' > <info>Password</info>: ' . str_repeat('*', mb_strlen($password)));
        } else {
            $password = $this->io->askHidden('Password (your type will be hidden)', [$this->validator, 'validatePassword']);
            $input->setArgument('password', $password);
        }

        $firstName = $input->getArgument('first-name');
        if ($firstName !== null) {
            $this->io->text(' > <info>First Name</info>: ' . $firstName);
        } else {
            $firstName = $this->io->ask('First Name', null, [$this->validator, 'validateFullName']);
            $input->setArgument('first-name', $firstName);
        }

        $lastName = $input->getArgument('last-name');
        if ($lastName !== null) {
            $this->io->text(' > <info>Last Name</info>: ' . $lastName);
        } else {
            $lastName = $this->io->ask('Last Name', null, [$this->validator, 'validateFullName']);
            $input->setArgument('last-name', $lastName);
        }

        $roles = $input->getArgument('roles');
        if ($roles !== null) {
            $this->io->text(' > <info>Roles(ROLE_USER,ROLE_ADMIN)</info>: ' . $roles);
        } else {
            $roles = $this->io->ask('Roles', null, [$this->validator, 'validateRoles']);
            $input->setArgument('roles', $roles);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stopwatch = new Stopwatch;
        $stopwatch->start('add-user-command');

        $username = $input->getArgument('username');
        $email = $input->getArgument('email');
        $plainPassword = $input->getArgument('password');
        $firstName = $input->getArgument('first-name');
        $lastName = $input->getArgument('last-name');
        $roles = $input->getArgument('roles');

        $this->validateUserData($username, $plainPassword, $email, $firstName, $lastName);

        $user = new User;
        $user->setEnabled(true);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setEmailCanonical($email);
        $user->setRoles(explode(',', $roles));
        $user->setSalt(HashHelper::generateUserSalt());

        $encodedPassword = $this->passwordEncoder->encodePassword($user, $plainPassword);
        $user->setPassword($encodedPassword);

        $this->entityManager->persistAndFlush($user);

        $this->io->success(sprintf('User was successfully created: %s (%s)', $user->getUsername(), $user->getEmail()));

        $event = $stopwatch->stop('add-user-command');
        if ($output->isVerbose()) {
            $this->io->comment(sprintf('New user database id: %d / Elapsed time: %.2f ms / Consumed memory: %.2f MB', $user->getId(), $event->getDuration(), $event->getMemory() / (1024 ** 2)));
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

    private function validInteractInputArguments(InputInterface $input): bool
    {
        foreach (['username', 'password', 'email', 'first-name', 'last-name', 'roles'] as $argument) {
            if ($input->getArgument($argument) === null) {
                return false;
            }
        }

        return true;
    }
}
