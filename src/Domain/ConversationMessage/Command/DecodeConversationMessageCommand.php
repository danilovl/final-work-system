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

namespace App\Domain\ConversationMessage\Command;

use App\Application\Service\EntityManagerService;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:html-decode-conversation-message', description: 'HTML decode conversation message.')]
class DecodeConversationMessageCommand extends Command
{
    public function __construct(private readonly EntityManagerService $entityManagerService)
    {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $offset = 0;
        $limit = 500;

        do {
            $query = "SELECT id, content FROM conversation_message LIMIT $offset, $limit";

            /** @var array{id: int, content: string} $messages */
            $messages = $this->entityManagerService
                ->getConnection()
                ->executeQuery($query)
                ->fetchAllAssociative();

            foreach ($messages as $message) {
                $id = $message['id'];
                $content = html_entity_decode($message['content']);

                $updateQuery = 'UPDATE conversation_message SET content = ? WHERE id = ?';
                $this->entityManagerService
                    ->getConnection()
                    ->executeQuery($updateQuery, [$content, $id]);
            }

            $offset += $limit;
        } while (!empty($messages));

        $output->writeln('Content decoding completed successfully.');

        return Command::SUCCESS;
    }
}
