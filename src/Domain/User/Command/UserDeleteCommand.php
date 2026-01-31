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

use App\Infrastructure\Service\EntityManagerService;
use App\Domain\User\Command\Validator\UserValidator;
use App\Domain\User\Entity\User;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:user-delete', description: 'Deletes users from the database')]
class UserDeleteCommand
{
    final public const string COMMAND_NAME = 'app:user-delete';

    public function __construct(
        private readonly EntityManagerService $entityManager,
        private readonly UserValidator $validator
    ) {}

    public function __invoke(
        SymfonyStyle $io,
        #[Argument(
            description: 'The username of an existing user',
            name: 'username'
        )]
        ?string $username = null
    ): int {
        if ($username === null) {
            $io->title('Delete user command');
            $username = $io->ask('Username', null, [$this->validator, 'validateUsernameExist']);
        }

        /** @var User|null $user */
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => $username]);

        if ($user === null) {
            $io->success(sprintf('User with username "%s" not found', $username));

            return Command::SUCCESS;
        }

        $this->entityManager->remove($user);

        $io->success(sprintf('User "%s" (ID: %d, email: %s) was successfully deleted', $user->getUsername(), $user->getId(), $user->getEmail()));

        return Command::SUCCESS;
    }
}
