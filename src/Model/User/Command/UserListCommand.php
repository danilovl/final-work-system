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

use App\Entity\User;
use App\Service\EntityManagerService;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{
    InputInterface,
    InputOption
};
use Symfony\Component\Console\Output\{
    BufferedOutput,
    OutputInterface
};
use Symfony\Component\Console\Style\SymfonyStyle;

class UserListCommand extends Command
{
    protected static $defaultName = 'app:user-list';

    public function __construct(private EntityManagerService $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Lists all the existing users')
            ->setHelp(<<<'HELP'
The <info>%command.name%</info> command lists all the users registered in the application:

  <info>php %command.full_name%</info>

By default the command only displays the 50 most recent users. Set the number of
results to display with the <comment>--max-results</comment> option:

  <info>php %command.full_name%</info> <comment>--max-results=2000</comment>

In addition to displaying the user list, you can also send this information to
the email address specified in the <comment>--send-to</comment> option:

  <info>php %command.full_name%</info> <comment>--send-to=fabien@symfony.com</comment>

HELP
            )
            ->addOption('max-result', 'mr', InputOption::VALUE_OPTIONAL, 'Limits the number of users listed', 50);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $maxResult = $input->getOption('max-result');
        $users = $this->entityManager
            ->getRepository(User::class)
            ->findBy([], ['id' => Criteria::DESC], $maxResult);

        $usersAsPlainArrays = array_map(fn(User $user): array => $this->userToArray($user), $users);

        $bufferedOutput = new BufferedOutput();
        $io = new SymfonyStyle($input, $bufferedOutput);
        $io->table(
            ['ID', 'Enabled', 'FirstName', 'LastName', 'Username', 'Email', 'Roles'],
            $usersAsPlainArrays
        );

        $output->write($bufferedOutput->fetch());

        return Command::SUCCESS;
    }

    private function userToArray(User $user): array
    {
        return [
            $user->getId(),
            $user->isEnabled(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getUsername(),
            $user->getEmail(),
            implode(', ', $user->getRoles())
        ];
    }
}
