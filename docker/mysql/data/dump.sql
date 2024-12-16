INSERT INTO `user` (`id`, `profile_image_id`, `username`, `roles`, `password`, `last_login`, `last_requested_at`, `confirmation_token`, `password_requested_at`, `username_canonical`, `email`, `email_canonical`, `enabled`, `enabled_email_notification`, `date_of_birth`, `firstname`, `lastname`, `website`, `biography`, `gender`, `locale`, `timezone`, `phone`, `token`, `salt`, `skype`, `degree_before`, `degree_after`, `message_greeting`, `message_signature`, `created_at`, `updated_at`) VALUES
(1, NULL, 'admin', '["ROLE_ADMIN"]', '$argon2id$v=19$m=65536,t=4,p=1$st5V6ICyVk+7b3f1ceqa8g$k9dNqI/n2eNwljU2uO1G4EB8WffPMHOmj4SqV+rCh8o', NULL, '2021-09-28 15:17:27', NULL, NULL, NULL, 'admin@admin.admin', 'admin@admin.admin', 1, 1, NULL, 'Admin', 'Admin', NULL, NULL, 'u', NULL, NULL, NULL, NULL, 'L5tqe1axZZJTIWN9KO76/7dQURNK/mLAfzvdzIIkzj8', NULL, NULL, NULL, NULL, NULL, '2021-09-28 15:16:48', '2021-09-28 15:17:27'),
(2, NULL, 'student', '["ROLE_STUDENT"]', '$argon2id$v=19$m=65536,t=4,p=1$dMtDiZ/R1HkDo7PUe4iZ0w$v/kuZxOiGg7ng16gNfregFoaUsCAbLxJY9CXpqaFXuI', NULL, NULL, NULL, NULL, NULL, 'student@student.student', 'student@student.student', 1, 1, NULL, 'student', 'student', NULL, NULL, 'u', NULL, NULL, NULL, NULL, 'JXHL1tFqsgwirfjpPVNgXYJdhgxrZY50PVq4GM2r8eQ', NULL, NULL, NULL, NULL, NULL, '2021-10-02 11:36:58', '2021-10-02 11:36:58'),
(3, NULL, 'supervisor', '["ROLE_SUPERVISOR"]', '$argon2id$v=19$m=65536,t=4,p=1$1AFmtAc3txKvESH5PcV7PA$IzIbz64906M9KDFOnG4Y5n6KhMKTqVVp5PXcsa11vP4', NULL, '2021-10-02 20:21:01', NULL, NULL, NULL, 'supervisor@supervisor.supervisor', 'supervisor@supervisor.supervisor', 1, 1, NULL, 'supervisor', 'supervisor', NULL, NULL, 'u', NULL, NULL, NULL, NULL, 'CxO0t9P68ixv/sgL9jRE47YJjZH09qIYHNt4/PGeCKg', NULL, NULL, NULL, NULL, NULL, '2021-10-02 11:37:26', '2021-10-02 20:21:01');
