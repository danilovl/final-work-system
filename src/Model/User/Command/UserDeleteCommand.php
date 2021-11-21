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

namespace App\Model\User\Command;

use App\Service\EntityManagerService;
use App\Util\Validator\UserValidator;
use App\Model\User\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{
    InputArgument,
    InputInterface
};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UserDeleteCommand extends Command
{
    protected static $defaultName = 'app:user-delete';

    private SymfonyStyle $io;

    public function __construct(
        private EntityManagerService $entityManager,
        private UserValidator $validator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Deletes users from the database')
            ->addArgument('username', InputArgument::REQUIRED, 'The username of an existing user');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if ($input->getArgument('username') !== null) {
            return;
        }

        $this->io->title('Delete user command');

        $username = $this->io->ask('Username', null, [$this->validator, 'validateUsernameExist']);
        $input->setArgument('username', $username);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');

        /** @var User|null $user */
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => $username]);

        if ($user === null) {
            $this->io->success(sprintf('User with username "%s" not found', $username));

            return Command::SUCCESS;
        }

        $this->entityManager->remove($user);

        $this->io->success(sprintf('User "%s" (ID: %d, email: %s) was successfully deleted', $user->getUsername(), $user->getId(), $user->getEmail()));

        return Command::SUCCESS;
    }
}
