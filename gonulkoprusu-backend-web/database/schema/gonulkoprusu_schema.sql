-- =====================================================================
--  Gönül Köprüsü - Unified Central Database Schema (MySQL 8.0+)
-- ---------------------------------------------------------------------
--  This DDL is the single source of truth shared by the Web (Laravel),
--  Android and iOS clients through the REST API.
--
--  Conventions:
--    * utf8mb4 / unicode collation for full Turkish + emoji support
--    * InnoDB engine for foreign-key integrity & transactions
--    * Soft, application-level enums kept as ENUM columns
--
--  Run with:  mysql -u <user> -p <database> < gonulkoprusu_schema.sql
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
--  users
--  PUBLIC fields  : username, profile_photo, city, district, gender
--  PRIVATE fields : first_name, last_name, email, phone  (owner+admin)
-- ---------------------------------------------------------------------
CREATE TABLE `users` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(50)  NOT NULL,            -- READ-ONLY after creation
  `first_name`    VARCHAR(100) NOT NULL,            -- PRIVATE
  `last_name`     VARCHAR(100) NOT NULL,            -- PRIVATE
  `email`         VARCHAR(190) NOT NULL,            -- PRIVATE
  `phone`         VARCHAR(30)  NOT NULL,            -- PRIVATE
  `password`      VARCHAR(255) NOT NULL,            -- bcrypt/argon hash
  `gender`        ENUM('male','female') NOT NULL,
  `city`          VARCHAR(80)  NOT NULL,
  `district`      VARCHAR(80)  NOT NULL,
  `profile_photo` VARCHAR(255) NULL,
  `bio`           VARCHAR(500) NULL,
  `role`          ENUM('user','admin') NOT NULL DEFAULT 'user',
  `status`        ENUM('active','banned') NOT NULL DEFAULT 'active',
  `is_premium`    TINYINT(1)   NOT NULL DEFAULT 0,  -- denormalized flag (men only)
  `last_login_at` TIMESTAMP NULL,
  `email_verified_at` TIMESTAMP NULL,
  `remember_token`    VARCHAR(100) NULL,
  `created_at`    TIMESTAMP NULL,
  `updated_at`    TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_gender_status_idx` (`gender`,`status`),
  KEY `users_city_district_idx` (`city`,`district`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  personal_access_tokens  (Laravel Sanctum - cross-platform auth)
-- ---------------------------------------------------------------------
CREATE TABLE `personal_access_tokens` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` VARCHAR(255) NOT NULL,
  `tokenable_id`   BIGINT UNSIGNED NOT NULL,
  `name`          VARCHAR(255) NOT NULL,
  `token`         VARCHAR(64)  NOT NULL,
  `abilities`     TEXT NULL,
  `last_used_at`  TIMESTAMP NULL,
  `expires_at`    TIMESTAMP NULL,
  `created_at`    TIMESTAMP NULL,
  `updated_at`    TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pat_token_unique` (`token`),
  KEY `pat_tokenable_idx` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  posts  (Instagram-like feed; comments are intentionally NOT modeled)
-- ---------------------------------------------------------------------
CREATE TABLE `posts` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     BIGINT UNSIGNED NOT NULL,
  `image_url`   VARCHAR(255) NOT NULL,
  `caption`     VARCHAR(500) NULL,
  `likes_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP NULL,
  `updated_at`  TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `posts_user_id_idx` (`user_id`),
  KEY `posts_created_at_idx` (`created_at`),
  CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  likes  (unique per user+post)
-- ---------------------------------------------------------------------
CREATE TABLE `likes` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `post_id`    BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `likes_user_post_unique` (`user_id`,`post_id`),
  KEY `likes_post_id_idx` (`post_id`),
  CONSTRAINT `fk_likes_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_likes_post` FOREIGN KEY (`post_id`)
    REFERENCES `posts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  stories  (Premium MEN only; auto-expire via expires_at)
-- ---------------------------------------------------------------------
CREATE TABLE `stories` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `media_url`  VARCHAR(255) NOT NULL,
  `expires_at` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `stories_user_id_idx` (`user_id`),
  KEY `stories_expires_at_idx` (`expires_at`),
  CONSTRAINT `fk_stories_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  reports  (Şikayet)
-- ---------------------------------------------------------------------
CREATE TABLE `reports` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `reporter_id` BIGINT UNSIGNED NOT NULL,
  `reported_id` BIGINT UNSIGNED NOT NULL,
  `reason`      VARCHAR(500) NOT NULL,
  `status`      ENUM('pending','reviewed','resolved','dismissed') NOT NULL DEFAULT 'pending',
  `created_at`  TIMESTAMP NULL,
  `updated_at`  TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `reports_status_idx` (`status`),
  KEY `reports_reported_idx` (`reported_id`),
  CONSTRAINT `fk_reports_reporter` FOREIGN KEY (`reporter_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reports_reported` FOREIGN KEY (`reported_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  blocks  (Engelle; unique per blocker+blocked)
-- ---------------------------------------------------------------------
CREATE TABLE `blocks` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `blocker_id` BIGINT UNSIGNED NOT NULL,
  `blocked_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blocks_pair_unique` (`blocker_id`,`blocked_id`),
  KEY `blocks_blocked_idx` (`blocked_id`),
  CONSTRAINT `fk_blocks_blocker` FOREIGN KEY (`blocker_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_blocks_blocked` FOREIGN KEY (`blocked_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  premium_subscriptions  (MEN only)
--   pro      = 1 Week   => 250 TL
--   gold     = 2 Weeks  => 300 TL
--   platinum = 1 Month  => 500 TL
-- ---------------------------------------------------------------------
CREATE TABLE `premium_subscriptions` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`      BIGINT UNSIGNED NOT NULL,
  `package_type` ENUM('pro','gold','platinum') NOT NULL,
  `price`        DECIMAL(8,2) NOT NULL,
  `started_at`   TIMESTAMP NULL,
  `expires_at`   TIMESTAMP NOT NULL,
  `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`   TIMESTAMP NULL,
  `updated_at`   TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `premium_user_idx` (`user_id`),
  KEY `premium_active_idx` (`is_active`,`expires_at`),
  CONSTRAINT `fk_premium_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  messages  (user-to-user; readable by Admin auditor)
-- ---------------------------------------------------------------------
CREATE TABLE `messages` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sender_id`    BIGINT UNSIGNED NOT NULL,
  `receiver_id`  BIGINT UNSIGNED NOT NULL,
  `message_text` TEXT NOT NULL,
  `is_read`      TINYINT(1) NOT NULL DEFAULT 0,
  `is_broadcast` TINYINT(1) NOT NULL DEFAULT 0,  -- official admin broadcast
  `created_at`   TIMESTAMP NULL,
  `updated_at`   TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `messages_pair_idx` (`sender_id`,`receiver_id`),
  KEY `messages_receiver_idx` (`receiver_id`,`is_read`),
  CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_messages_receiver` FOREIGN KEY (`receiver_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
--  Reference data (optional seed): pricing matrix
-- =====================================================================
-- pro      => 7  days  => 250.00 TL
-- gold     => 14 days  => 300.00 TL
-- platinum => 30 days  => 500.00 TL
