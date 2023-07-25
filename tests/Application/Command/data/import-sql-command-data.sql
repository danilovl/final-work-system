CREATE TABLE IF NOT EXISTS `import_sql_command_test`
(
    `id`        INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `firstname` VARCHAR(30) NOT NULL,
    `lastname`  VARCHAR(30) NOT NULL,
    `email`     VARCHAR(50)
);

INSERT INTO `import_sql_command_test` (`firstname`, `lastname`, `email`)
VALUES ('firstname', 'lastname', 'firstname@email.com');

DROP TABLE `import_sql_command_test`;
