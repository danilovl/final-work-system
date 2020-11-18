<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201118082319 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE `user` ADD `last_requested_at` DATETIME DEFAULT NULL AFTER `last_login`');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE `user` DROP `last_requested_at`');
    }
}
