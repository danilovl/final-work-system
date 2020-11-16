<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201116202559 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `system_event_type` (`id`, `group`, `name`, `description`, `constant`, `created_at`, `update_at`) VALUES
                            (19, 'ukoly', 'TASK_REMIND_DEADLINE', NULL, 'TASK_REMIND_DEADLINE', NOW(), NOW());");
    }
}
