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

namespace FinalWork\FinalWorkBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use FinalWork\FinalWorkBundle\Utils\Validator\UserValidator;
use FinalWork\SonataUserBundle\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{
    InputArgument,
    InputInterface
};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeleteUserCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'finalwork:delete-user';

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserValidator
     */
    private $validator;

    /**
     * DeleteUserCommand constructor.
     * @param EntityManagerInterface $em
     * @param UserValidator $userValidator
     */
    public function __construct(EntityManagerInterface $em, UserValidator $userValidator)
    {
        parent::__construct();

        $this->entityManager = $em;
        $this->validator = $userValidator;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Deletes users from the database')
            ->addArgument('username', InputArgument::REQUIRED, 'The username of an existing user');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if ($input->getArgument('username') !== null) {
            return;
        }

        $this->io->title('Delete user command');

        $username = $this->io->ask('Username', null, [$this->validator, 'validateUsernameExist']);
        $input->setArgument('username', $username);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');

        /** @var User $user */
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy([
                'username' => $username
            ]);

        $userId = $user->getId();

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->io->success(sprintf('User "%s" (ID: %d, email: %s) was successfully deleted', $user->getUsername(), $userId, $user->getEmail()));
    }
}
