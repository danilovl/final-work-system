<?php declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200627193807 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `conversation_type` (`id`, `name`, `description`, `constant`, `created_at`, `updated_at`) VALUES 
            (1, 'WORK', NULL, 'WORK', NOW(), NULL),
            (2, 'GROUP', NULL, 'GROUP', NOW(), NULL);
        ");

        $this->addSql("INSERT INTO `event_type` (`id`, `color`, `registrable`, `name`, `description`, `constant`, `created_at`, `updated_at`) VALUES 
            (1, '#3b91ad', 1, 'Consultation', NULL, 'CONSULTATION', NOW(), NULL),
            (2, '#cab31c', 0, 'Personal calendar', NULL, 'PERSONAL', NOW(), NULL);
       ");

        $this->addSql("INSERT INTO `media_mime_type` (`id`, `name`, `extension`, `active`, `created_at`, `updated_at`) VALUES 
            (1, 'image/jpeg', 'jpg', 1, NOW(), NULL),
            (2, 'image/png', 'png', 1, NOW(), NULL),
            (3, 'application/msword', 'doc', 1, NOW(), NULL),
            (4, 'application/pdf', 'pdf', 1, NOW(), NULL),
            (5, 'application/zip', 'zip', 1, NOW(), NULL),
            (6, 'video/x-msvideo', 'avi', 0, NOW(), NOW()),
            (7, 'video/mp4', 'mp4', 0, NOW(), NOW()),
            (8, 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'pptx', 1, NOW(), NULL),
            (9, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'docx', 1, NOW(), NULL),
            (10, 'application/vnd.ms-excel', 'xls', 1, NOW(), NULL),
            (11, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'xlsx', 1, NOW(), NULL),
            (12, 'application/vnd.ms-powerpoint', 'ppt', 1, NOW(), NULL),
            (13, 'application/vnd.ms-word.template.macroEnabled.12', 'dotm', 1, NOW(), NULL),
            (14, 'application/vnd.ms-word.document.macroEnabled.12', 'docm', 1, NOW(), NULL),
            (15, 'application/octet-stream', 'astah', 1, NOW(), NULL),
            (16, 'application/x-rar-compressed', 'rar', 1, NOW(), NULL),
            (17, 'text/x-java-properties', 'properties', 1, NOW(), NULL);
        ");

        $this->addSql("INSERT INTO `media_type` (`id`, `folder`, `name`, `description`, `constant`, `created_at`, `updated_at`) VALUES 
            (1, 'version', 'WORK_VERSION', NULL, 'WORK_VERSION', NOW(), NULL),
            (2, 'document', 'INFORMATION_MATERIAL', NULL, 'INFORMATION_MATERIAL', NOW(), NULL),
            (3, 'profile_image', 'USER_PROFILE_IMAGE', NULL, 'USER_PROFILE_IMAGE', NOW(), NULL),
            (4, 'article', 'ARTICLE', NULL, 'ARTICLE', NOW(), NULL);
        ");

        $this->addSql("INSERT INTO `system_event_type` (`id`, `group`, `name`, `description`, `constant`, `created_at`, `updated_at`) VALUES 
            (1, 'work', 'WORK_CREATE', NULL, 'WORK_CREATE', NOW(), NULL),
            (2, 'work', 'WORK_EDIT', NULL, 'WORK_EDIT', NOW(), NULL),
            (3, 'user', 'USER_EDIT', NULL, 'USER_EDIT', NOW(), NULL),
            (4, 'tasks', 'TASK_CREATE', NULL, 'TASK_CREATE', NOW(), NULL),
            (5, 'task', 'TASK_EDIT', NULL, 'TASK_EDIT', NOW(), NULL),
            (6, 'task', 'TASK_COMPLETE', NULL, 'TASK_COMPLETE', NOW(), NULL),
            (7, 'task', 'TASK_INCOMPLETE', NULL, 'TASK_INCOMPLETE', NOW(), NULL),
            (8, 'task', 'TASK_NOTIFY_COMPLETE', NULL, 'TASK_NOTIFY_COMPLETE', NOW(), NULL),
            (9, 'task', 'TASK_NOTIFY_INCOMPLETE', NULL, 'TASK_NOTIFY_INCOMPLETE', NOW(), NULL),
            (10, 'version', 'VERSION_CREATE', NULL, 'VERSION_CREATE', NOW(), NULL),
            (11, 'version', 'VERSION_EDIT', NULL, 'VERSION_EDIT', NOW(), NULL),
            (12, 'document', 'DOCUMENT_CREATE', NULL, 'DOCUMENT_CREATE', NOW(), NULL),
            (13, 'event', 'EVENT_CREATE', NULL, 'EVENT_CREATE', NOW(), NULL),
            (14, 'event', 'EVENT_EDIT', NULL, 'EVENT_EDIT', NOW(), NULL),
            (15, 'event', 'EVENT_SWITCH_SKYPE', NULL, 'EVENT_SWITCH_SKYPE', NOW(), NULL),
            (16, 'event', 'EVENT_COMMENT_CREATE', NULL, 'EVENT_COMMENT_CREATE', NOW(), NULL),
            (17, 'event', 'EVENT_COMMENT_EDIT', NULL, 'EVENT_COMMENT_EDIT', NOW(), NULL),
            (18, 'message', 'MESSAGE_CREATE', NULL, 'MESSAGE_CREATE', NOW(), NULL);
            (19, 'tasks', 'TASK_REMIND_DEADLINE', NULL, 'TASK_REMIND_DEADLINE', NOW(), NULL);
        ");

        $this->addSql("INSERT INTO `work_status` (`id`, `color`, `name`, `description`, `constant`, `created_at`, `updated_at`) VALUES 
            (1, '#42f442', 'Active', NULL, 'ACTIVE', NOW(), NULL),
            (2, '#ef0202', 'Archive', NULL, 'ARCHIVE', NOW(), NULL),
            (3, '#4f5dff', 'Helpers', NULL, 'AUXILIARY', '2017-09-16 22:16:34', NULL),
            (4, '#fbff49', 'Preliminary', NULL, 'PRELIMINARY', '2017-09-16 22:16:35', NULL),
            (5, '#c4c5d1', 'Uncategorized', NULL, 'UNCLASSIFIED', '2017-09-16 22:16:35', NULL);
        ");

        $this->addSql("INSERT INTO `work_type` (`id`, `shortcut`, `name`, `description`, `created_at`, `updated_at`) VALUES 
                (1, 'DT', 'Diploma thesis', NULL, NOW(), NULL),
                (2, 'BT', 'Bachelor thesis', NULL, NOW(), NULL),
                (3, 'Other', 'Other', NULL, NOW(), NULL);
        ");

        $this->addSql("INSERT INTO `conversation_message_status_type` (`id`, `name`, `description`, `constant`, `created_at`, `updated_at`) VALUES 
                (1, 'read', NULL, 'READ', NOW(), NULL),
                (2, 'unread', NULL, 'UNREAD', NOW(), NULL);
        ");
    }

    public function down(Schema $schema): void
    {
    }
}
