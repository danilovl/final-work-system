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
use App\Domain\User\Entity\User;
use Doctrine\Common\Collections\Order;
use Symfony\Component\Console\Attribute\{
    Option,
    AsCommand
};
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user-list',
    description: 'Lists all the existing users',
    help: <<<TXT
The <info>%command.name%</info> command lists all the users registered in the application:

  <info>php %command.full_name%</info>

By default the command only displays the 50 most recent users. Set the number of
results to display with the <comment>--max-results</comment> option:

  <info>php %command.full_name%</info> <comment>--max-results=2000</comment>

In addition to displaying the user list, you can also send this information to
the email address specified in the <comment>--send-to</comment> option:

  <info>php %command.full_name%</info> <comment>--send-to=fabien@symfony.com</comment>
TXT
)]
class UserListCommand
{
    final public const string COMMAND_NAME = 'app:user-list';

    public function __construct(private readonly EntityManagerService $entityManager) {}

    public function __invoke(
        SymfonyStyle $io,
        #[Option(
            description: 'Limits the number of users listed',
            name: 'max-result',
            shortcut: 'mr'
        )]
        int $maxResult = 50
    ): int {
        $users = $this->entityManager
            ->getRepository(User::class)
            ->findBy([], ['id' => Order::Descending->value], $maxResult);

        $usersAsPlainArrays = array_map(fn (User $user): array => $this->userToArray($user), $users);

        $io->table(
            ['ID', 'Enabled', 'FirstName', 'LastName', 'Username', 'Email', 'Roles'],
            $usersAsPlainArrays
        );

        return Command::SUCCESS;
    }

    /**
     * @return array{int, bool, string, string, string, string, string}
     */
    private function userToArray(User $user): array
    {
        return [
            $user->getId(),
            $user->isEnabled(),
            $user->getFirstname(),
            $user->getLastName(),
            $user->getUsername(),
            $user->getEmail(),
            implode(', ', $user->getRoles())
        ];
    }
}
