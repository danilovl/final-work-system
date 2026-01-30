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

use App\Infrastructure\Service\EntityManagerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:html-decode-conversation-message', description: 'HTML decode conversation message.')]
class DecodeConversationMessageCommand
{
    final public const string COMMAND_NAME = 'app:html-decode-conversation-message';

    public function __construct(private readonly EntityManagerService $entityManagerService)
    {
    }

    public function __invoke(SymfonyStyle $io): int
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

        $io->success('Content decoding completed successfully.');

        return Command::SUCCESS;
    }
}
