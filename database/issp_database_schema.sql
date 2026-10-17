-- =============================================================================
--  ISSP (Insurance Sector Strengthening Program) — issprogram.com
--  Relational Database Schema
-- =============================================================================
--  RDBMS        : MySQL 8.0+ / MariaDB 10.5+ (mysqli compatible)
--  Charset      : utf8mb4  (full Unicode, emoji-safe)
--  Collation    : utf8mb4_unicode_ci
--  Engine       : InnoDB   (transactions + foreign keys)
--  Conventions  :
--    - snake_case, plural table names
--    - surrogate PK: id BIGINT UNSIGNED AUTO_INCREMENT
--    - public-facing UUID column on every user-/content-facing table
--      (never expose sequential integer IDs in URLs — OWASP IDOR guidance)
--    - audit columns: created_at, updated_at, created_by, updated_by
--    - soft deletes (deleted_at) on content the business may need to recover
--    - all dates/times stored in UTC; convert to Africa/Lagos in the app layer
--    - all FKs explicitly named, with sensible ON DELETE / ON UPDATE actions
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_ALL_TABLES,NO_ENGINE_SUBSTITUTION,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';

CREATE DATABASE IF NOT EXISTS `issp_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `issp_db`;

-- =============================================================================
-- SECTION 1: ACCESS CONTROL — roles, permissions, organizations, users
-- =============================================================================

-- ---------------------------------------------------------------------------
-- roles
-- Fixed set of roles used across both the CMS admin side and the stakeholder
-- portal, e.g. super_admin, admin, editor, insurer, regulator, partner.
-- ---------------------------------------------------------------------------
CREATE TABLE `roles` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`          CHAR(36)        NOT NULL DEFAULT (UUID()),
  `name`          VARCHAR(60)     NOT NULL COMMENT 'e.g. super_admin, admin, editor, stakeholder_insurer, stakeholder_regulator, stakeholder_partner',
  `display_name`  VARCHAR(100)    NOT NULL,
  `description`   VARCHAR(255)             DEFAULT NULL,
  `is_system`     TINYINT(1)      NOT NULL DEFAULT 0 COMMENT '1 = protected role, cannot be deleted via UI',
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_uuid` (`uuid`),
  UNIQUE KEY `uq_roles_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- permissions
-- Granular capability flags (e.g. pages.publish, resources.upload).
-- ---------------------------------------------------------------------------
CREATE TABLE `permissions` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(100)    NOT NULL COMMENT 'dot.notation, e.g. pages.publish',
  `display_name`  VARCHAR(150)    NOT NULL,
  `module`        VARCHAR(60)     NOT NULL COMMENT 'groups permissions in the admin UI, e.g. pages, resources, events',
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_permissions_name` (`name`),
  KEY `idx_permissions_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- role_permissions (many-to-many)
-- ---------------------------------------------------------------------------
CREATE TABLE `role_permissions` (
  `role_id`       BIGINT UNSIGNED NOT NULL,
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`, `permission_id`),
  CONSTRAINT `fk_role_permissions_role`
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_role_permissions_permission`
    FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- organizations
-- The insurers, regulators, partner bodies, and donor agencies whose staff
-- use the stakeholder login area. One organization has many user accounts.
-- ---------------------------------------------------------------------------
CREATE TABLE `organizations` (
  `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`                CHAR(36)        NOT NULL DEFAULT (UUID()),
  `name`                VARCHAR(180)    NOT NULL,
  `organization_type`   ENUM('insurer','regulator','partner','donor','government','other')
                                        NOT NULL DEFAULT 'other',
  `registration_number` VARCHAR(100)             DEFAULT NULL,
  `website_url`         VARCHAR(255)             DEFAULT NULL,
  `contact_email`       VARCHAR(190)             DEFAULT NULL,
  `contact_phone`       VARCHAR(30)              DEFAULT NULL,
  `address`             VARCHAR(255)             DEFAULT NULL,
  `country`             VARCHAR(80)     NOT NULL DEFAULT 'Nigeria',
  `is_active`           TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`          DATETIME                 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_organizations_uuid` (`uuid`),
  KEY `idx_organizations_type` (`organization_type`),
  KEY `idx_organizations_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- users
-- Single table for both CMS staff and stakeholder-portal users, distinguished
-- by `user_type`. organization_id is populated only for stakeholder accounts.
-- ---------------------------------------------------------------------------
CREATE TABLE `users` (
  `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`                  CHAR(36)        NOT NULL DEFAULT (UUID()),
  `organization_id`       BIGINT UNSIGNED          DEFAULT NULL COMMENT 'NULL for internal CMS staff',
  `user_type`             ENUM('staff','stakeholder') NOT NULL DEFAULT 'stakeholder',
  `first_name`            VARCHAR(100)    NOT NULL,
  `last_name`             VARCHAR(100)    NOT NULL,
  `email`                 VARCHAR(190)    NOT NULL,
  `phone`                 VARCHAR(30)              DEFAULT NULL,
  `job_title`             VARCHAR(150)             DEFAULT NULL,
  `password_hash`         VARCHAR(255)    NOT NULL COMMENT 'PHP password_hash() — bcrypt/argon2id, never store plaintext',
  `avatar_media_id`       BIGINT UNSIGNED          DEFAULT NULL,
  `status`                ENUM('pending','active','suspended','deactivated') NOT NULL DEFAULT 'pending',
  `email_verified_at`     DATETIME                 DEFAULT NULL,
  `must_change_password`  TINYINT(1)      NOT NULL DEFAULT 0,
  `two_factor_secret`     VARCHAR(255)             DEFAULT NULL COMMENT 'encrypted at rest, used for optional 2FA on stakeholder/admin accounts',
  `two_factor_enabled`    TINYINT(1)      NOT NULL DEFAULT 0,
  `failed_login_attempts` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `locked_until`          DATETIME                 DEFAULT NULL,
  `last_login_at`         DATETIME                 DEFAULT NULL,
  `last_login_ip`         VARCHAR(45)              DEFAULT NULL COMMENT 'VARCHAR(45) holds IPv6',
  `remember_token_hash`   VARCHAR(255)             DEFAULT NULL,
  `created_at`            DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`            DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`            DATETIME                 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_uuid` (`uuid`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_organization` (`organization_id`),
  KEY `idx_users_type_status` (`user_type`, `status`),
  KEY `idx_users_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_users_organization`
    FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- user_roles (many-to-many)
-- ---------------------------------------------------------------------------
CREATE TABLE `user_roles` (
  `user_id`     BIGINT UNSIGNED NOT NULL,
  `role_id`     BIGINT UNSIGNED NOT NULL,
  `assigned_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `assigned_by` BIGINT UNSIGNED          DEFAULT NULL,
  PRIMARY KEY (`user_id`, `role_id`),
  CONSTRAINT `fk_user_roles_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_user_roles_role`
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_user_roles_assigned_by`
    FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- password_resets
-- ---------------------------------------------------------------------------
CREATE TABLE `password_resets` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     BIGINT UNSIGNED NOT NULL,
  `token_hash`  VARCHAR(255)    NOT NULL COMMENT 'store a hash of the token, never the raw token',
  `expires_at`  DATETIME        NOT NULL,
  `used_at`     DATETIME                 DEFAULT NULL,
  `requested_ip` VARCHAR(45)             DEFAULT NULL,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_password_resets_user` (`user_id`),
  KEY `idx_password_resets_expires` (`expires_at`),
  CONSTRAINT `fk_password_resets_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- login_audit_logs
-- Security/compliance trail of every authentication attempt — important for
-- an insurance-sector platform with regulator/insurer logins.
-- ---------------------------------------------------------------------------
CREATE TABLE `login_audit_logs` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`         BIGINT UNSIGNED          DEFAULT NULL COMMENT 'NULL if the email did not match any account',
  `email_attempted` VARCHAR(190)    NOT NULL,
  `status`          ENUM('success','failed_password','failed_locked','failed_unverified','failed_inactive')
                                    NOT NULL,
  `ip_address`      VARCHAR(45)              DEFAULT NULL,
  `user_agent`      VARCHAR(255)             DEFAULT NULL,
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_login_audit_user` (`user_id`),
  KEY `idx_login_audit_created` (`created_at`),
  CONSTRAINT `fk_login_audit_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 2: MEDIA LIBRARY
-- =============================================================================

CREATE TABLE `media` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`            CHAR(36)        NOT NULL DEFAULT (UUID()),
  `disk`            VARCHAR(30)     NOT NULL DEFAULT 'local' COMMENT 'local, s3, etc.',
  `file_path`       VARCHAR(500)    NOT NULL,
  `file_name`       VARCHAR(255)    NOT NULL,
  `mime_type`       VARCHAR(120)    NOT NULL,
  `file_size_bytes` BIGINT UNSIGNED NOT NULL,
  `alt_text`        VARCHAR(255)             DEFAULT NULL,
  `width_px`        INT UNSIGNED             DEFAULT NULL,
  `height_px`       INT UNSIGNED             DEFAULT NULL,
  `uploaded_by`     BIGINT UNSIGNED          DEFAULT NULL,
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`      DATETIME                 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_media_uuid` (`uuid`),
  KEY `idx_media_uploaded_by` (`uploaded_by`),
  KEY `idx_media_mime_type` (`mime_type`),
  CONSTRAINT `fk_media_uploaded_by`
    FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_avatar_media`
  FOREIGN KEY (`avatar_media_id`) REFERENCES `media` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- =============================================================================
-- SECTION 3: CMS — pages, revisions, navigation
-- =============================================================================

-- ---------------------------------------------------------------------------
-- pages
-- Covers Home, About, Programme Pillars, Team, Partners, Contact, etc.
-- Flexible `body_content` (HTML/JSON blocks) lets non-developers edit via CMS.
-- ---------------------------------------------------------------------------
CREATE TABLE `pages` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`              CHAR(36)        NOT NULL DEFAULT (UUID()),
  `parent_id`         BIGINT UNSIGNED          DEFAULT NULL COMMENT 'self-referencing, for nested pages',
  `title`             VARCHAR(200)    NOT NULL,
  `slug`              VARCHAR(220)    NOT NULL COMMENT 'URL path segment, unique',
  `excerpt`           VARCHAR(500)             DEFAULT NULL,
  `body_content`      LONGTEXT                 DEFAULT NULL COMMENT 'HTML/JSON block content rendered on the frontend',
  `featured_media_id` BIGINT UNSIGNED          DEFAULT NULL,
  `template`          VARCHAR(100)    NOT NULL DEFAULT 'default' COMMENT 'maps to a frontend Blade/PHP template file',
  `status`            ENUM('draft','pending_review','published','archived') NOT NULL DEFAULT 'draft',
  `meta_title`        VARCHAR(200)             DEFAULT NULL,
  `meta_description`  VARCHAR(300)             DEFAULT NULL,
  `og_image_media_id` BIGINT UNSIGNED          DEFAULT NULL,
  `sort_order`        INT UNSIGNED    NOT NULL DEFAULT 0,
  `published_at`      DATETIME                 DEFAULT NULL,
  `created_by`        BIGINT UNSIGNED          DEFAULT NULL,
  `updated_by`        BIGINT UNSIGNED          DEFAULT NULL,
  `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`        DATETIME                 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pages_uuid` (`uuid`),
  UNIQUE KEY `uq_pages_slug` (`slug`),
  KEY `idx_pages_parent` (`parent_id`),
  KEY `idx_pages_status` (`status`),
  KEY `idx_pages_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_pages_parent`
    FOREIGN KEY (`parent_id`) REFERENCES `pages` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pages_featured_media`
    FOREIGN KEY (`featured_media_id`) REFERENCES `media` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pages_og_image_media`
    FOREIGN KEY (`og_image_media_id`) REFERENCES `media` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pages_created_by`
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pages_updated_by`
    FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- page_revisions
-- Version history so edits can be rolled back (standard CMS requirement).
-- ---------------------------------------------------------------------------
CREATE TABLE `page_revisions` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_id`       BIGINT UNSIGNED NOT NULL,
  `title`         VARCHAR(200)    NOT NULL,
  `body_content`  LONGTEXT                 DEFAULT NULL,
  `edited_by`     BIGINT UNSIGNED          DEFAULT NULL,
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_page_revisions_page` (`page_id`),
  CONSTRAINT `fk_page_revisions_page`
    FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_page_revisions_edited_by`
    FOREIGN KEY (`edited_by`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- menus & menu_items
-- Lets the client reorder/rename the main navigation without a developer.
-- ---------------------------------------------------------------------------
CREATE TABLE `menus` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100)    NOT NULL COMMENT 'e.g. primary_navigation, footer_menu',
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_menus_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `menu_items` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `menu_id`     BIGINT UNSIGNED NOT NULL,
  `parent_id`   BIGINT UNSIGNED          DEFAULT NULL COMMENT 'self-referencing, for dropdown sub-items',
  `page_id`     BIGINT UNSIGNED          DEFAULT NULL COMMENT 'NULL when linking to a custom_url instead',
  `label`       VARCHAR(100)    NOT NULL,
  `custom_url`  VARCHAR(255)             DEFAULT NULL,
  `open_new_tab` TINYINT(1)     NOT NULL DEFAULT 0,
  `sort_order`  INT UNSIGNED    NOT NULL DEFAULT 0,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_menu_items_menu` (`menu_id`),
  KEY `idx_menu_items_parent` (`parent_id`),
  CONSTRAINT `fk_menu_items_menu`
    FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_menu_items_parent`
    FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_menu_items_page`
    FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 4: PROGRAMME CONTENT — pillars, impact metrics, FAQs
-- =============================================================================

-- ---------------------------------------------------------------------------
-- programme_pillars
-- The 7 pillars (Advocacy & Policy, Capacity Building, Youth Engagement, ...)
-- ---------------------------------------------------------------------------
CREATE TABLE `programme_pillars` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`         CHAR(36)        NOT NULL DEFAULT (UUID()),
  `title`        VARCHAR(150)    NOT NULL,
  `slug`         VARCHAR(170)    NOT NULL,
  `summary`      VARCHAR(500)             DEFAULT NULL,
  `body_content` LONGTEXT                 DEFAULT NULL,
  `icon_media_id` BIGINT UNSIGNED         DEFAULT NULL,
  `sort_order`   INT UNSIGNED    NOT NULL DEFAULT 0,
  `is_published` TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`   DATETIME                 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pillars_uuid` (`uuid`),
  UNIQUE KEY `uq_pillars_slug` (`slug`),
  CONSTRAINT `fk_pillars_icon_media`
    FOREIGN KEY (`icon_media_id`) REFERENCES `media` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- impact_metrics
-- The homepage KPI counters (penetration target, Nigerians reached, etc.),
-- editable from the CMS instead of hardcoded in the frontend.
-- ---------------------------------------------------------------------------
CREATE TABLE `impact_metrics` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `label`         VARCHAR(150)    NOT NULL COMMENT 'e.g. Insurance Penetration, MSME Coverage',
  `display_value` VARCHAR(60)     NOT NULL COMMENT 'e.g. 0.5% -> 1.5%, 25M+, 250,000',
  `description`   VARCHAR(255)             DEFAULT NULL,
  `sort_order`    INT UNSIGNED    NOT NULL DEFAULT 0,
  `is_published`  TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- faqs
-- ---------------------------------------------------------------------------
CREATE TABLE `faqs` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `question`     VARCHAR(300)    NOT NULL,
  `answer`       TEXT            NOT NULL,
  `sort_order`   INT UNSIGNED    NOT NULL DEFAULT 0,
  `is_published` TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 5: PEOPLE — team members & partners
-- =============================================================================

CREATE TABLE `team_members` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`            CHAR(36)        NOT NULL DEFAULT (UUID()),
  `full_name`       VARCHAR(150)    NOT NULL,
  `role_title`      VARCHAR(200)    NOT NULL COMMENT 'e.g. Project Director, Advisory Board Member',
  `group`           ENUM('leadership','advisory_board','secretariat') NOT NULL DEFAULT 'leadership',
  `bio`             TEXT                     DEFAULT NULL,
  `photo_media_id`  BIGINT UNSIGNED          DEFAULT NULL,
  `linkedin_url`    VARCHAR(255)             DEFAULT NULL,
  `email`           VARCHAR(190)             DEFAULT NULL,
  `sort_order`      INT UNSIGNED    NOT NULL DEFAULT 0,
  `is_published`    TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`      DATETIME                 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_team_members_uuid` (`uuid`),
  KEY `idx_team_members_group` (`group`),
  CONSTRAINT `fk_team_members_photo_media`
    FOREIGN KEY (`photo_media_id`) REFERENCES `media` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `partners` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`            CHAR(36)        NOT NULL DEFAULT (UUID()),
  `organization_id` BIGINT UNSIGNED          DEFAULT NULL COMMENT 'link to organizations when the partner also has portal access',
  `name`            VARCHAR(180)    NOT NULL,
  `partner_type`    ENUM('implementing_partner','donor','institutional_host','advisory','other') NOT NULL DEFAULT 'other',
  `logo_media_id`   BIGINT UNSIGNED          DEFAULT NULL,
  `website_url`     VARCHAR(255)             DEFAULT NULL,
  `description`     VARCHAR(500)             DEFAULT NULL,
  `sort_order`      INT UNSIGNED    NOT NULL DEFAULT 0,
  `is_published`    TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`       DATETIME                DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_partners_uuid` (`uuid`),
  KEY `idx_partners_type` (`partner_type`),
  CONSTRAINT `fk_partners_organization`
    FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_partners_logo_media`
    FOREIGN KEY (`logo_media_id`) REFERENCES `media` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 6: NEWS / UPDATES (blog)
-- =============================================================================

CREATE TABLE `post_categories` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100)    NOT NULL,
  `slug`        VARCHAR(120)    NOT NULL,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_post_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tags` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(80)     NOT NULL,
  `slug`        VARCHAR(100)    NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tags_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `posts` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`              CHAR(36)        NOT NULL DEFAULT (UUID()),
  `category_id`       BIGINT UNSIGNED          DEFAULT NULL,
  `author_id`         BIGINT UNSIGNED          DEFAULT NULL,
  `title`             VARCHAR(220)    NOT NULL,
  `slug`              VARCHAR(240)    NOT NULL,
  `excerpt`           VARCHAR(500)             DEFAULT NULL,
  `body_content`      LONGTEXT        NOT NULL,
  `featured_media_id` BIGINT UNSIGNED          DEFAULT NULL,
  `status`            ENUM('draft','pending_review','published','archived') NOT NULL DEFAULT 'draft',
  `meta_title`        VARCHAR(200)             DEFAULT NULL,
  `meta_description`  VARCHAR(300)             DEFAULT NULL,
  `view_count`        BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `published_at`      DATETIME                 DEFAULT NULL,
  `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`        DATETIME                 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_posts_uuid` (`uuid`),
  UNIQUE KEY `uq_posts_slug` (`slug`),
  KEY `idx_posts_category` (`category_id`),
  KEY `idx_posts_author` (`author_id`),
  KEY `idx_posts_status_published` (`status`, `published_at`),
  FULLTEXT KEY `ftx_posts_title_body` (`title`, `excerpt`),
  CONSTRAINT `fk_posts_category`
    FOREIGN KEY (`category_id`) REFERENCES `post_categories` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_posts_author`
    FOREIGN KEY (`author_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_posts_featured_media`
    FOREIGN KEY (`featured_media_id`) REFERENCES `media` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `post_tags` (
  `post_id` BIGINT UNSIGNED NOT NULL,
  `tag_id`  BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`post_id`, `tag_id`),
  CONSTRAINT `fk_post_tags_post`
    FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_post_tags_tag`
    FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 7: RESOURCES / DOWNLOADS LIBRARY
-- Policy briefs, reports, concept notes — some public, some stakeholder-only.
-- =============================================================================

CREATE TABLE `resource_categories` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100)    NOT NULL COMMENT 'e.g. Policy Brief, Annual Report, Concept Note',
  `slug`        VARCHAR(120)    NOT NULL,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_resource_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `resources` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`            CHAR(36)        NOT NULL DEFAULT (UUID()),
  `category_id`     BIGINT UNSIGNED          DEFAULT NULL,
  `title`           VARCHAR(220)    NOT NULL,
  `slug`            VARCHAR(240)    NOT NULL,
  `description`     VARCHAR(500)             DEFAULT NULL,
  `file_media_id`   BIGINT UNSIGNED NOT NULL COMMENT 'the downloadable file itself',
  `cover_media_id`  BIGINT UNSIGNED          DEFAULT NULL COMMENT 'optional thumbnail/cover image',
  `publication_year` YEAR                    DEFAULT NULL,
  `visibility`      ENUM('public','stakeholder','private') NOT NULL DEFAULT 'public'
                      COMMENT 'public = open download; stakeholder = requires login; private = internal only',
  `download_count`  BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `uploaded_by`     BIGINT UNSIGNED          DEFAULT NULL,
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`      DATETIME                 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_resources_uuid` (`uuid`),
  UNIQUE KEY `uq_resources_slug` (`slug`),
  KEY `idx_resources_category` (`category_id`),
  KEY `idx_resources_visibility` (`visibility`),
  CONSTRAINT `fk_resources_category`
    FOREIGN KEY (`category_id`) REFERENCES `resource_categories` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_resources_file_media`
    FOREIGN KEY (`file_media_id`) REFERENCES `media` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_resources_cover_media`
    FOREIGN KEY (`cover_media_id`) REFERENCES `media` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_resources_uploaded_by`
    FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- resource_access_grants
-- For `visibility = stakeholder` resources, optionally restrict further to
-- specific organizations or roles (e.g. a document only for regulators).
-- An empty result set for a resource means "any authenticated stakeholder".
-- ---------------------------------------------------------------------------
CREATE TABLE `resource_access_grants` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `resource_id`     BIGINT UNSIGNED NOT NULL,
  `organization_id` BIGINT UNSIGNED          DEFAULT NULL,
  `role_id`         BIGINT UNSIGNED          DEFAULT NULL,
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_resource_access_resource` (`resource_id`),
  KEY `idx_resource_access_org` (`organization_id`),
  KEY `idx_resource_access_role` (`role_id`),
  CONSTRAINT `fk_resource_access_resource`
    FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_resource_access_org`
    FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_resource_access_role`
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- resource_download_logs
-- Audit trail of who downloaded what and when — standard practice when
-- regulators/insurers access controlled documents.
-- ---------------------------------------------------------------------------
CREATE TABLE `resource_download_logs` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `resource_id`  BIGINT UNSIGNED NOT NULL,
  `user_id`      BIGINT UNSIGNED          DEFAULT NULL COMMENT 'NULL for anonymous public downloads',
  `ip_address`   VARCHAR(45)              DEFAULT NULL,
  `user_agent`   VARCHAR(255)             DEFAULT NULL,
  `downloaded_at` DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_download_logs_resource` (`resource_id`),
  KEY `idx_download_logs_user` (`user_id`),
  CONSTRAINT `fk_download_logs_resource`
    FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_download_logs_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 8: EVENT CALENDAR
-- =============================================================================

CREATE TABLE `event_categories` (
  `id`    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`  VARCHAR(100)    NOT NULL COMMENT 'e.g. Programme Launch, Training, Webinar',
  `slug`  VARCHAR(120)    NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_event_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `events` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`              CHAR(36)        NOT NULL DEFAULT (UUID()),
  `category_id`       BIGINT UNSIGNED          DEFAULT NULL,
  `title`             VARCHAR(220)    NOT NULL,
  `slug`              VARCHAR(240)    NOT NULL,
  `description`       TEXT                     DEFAULT NULL,
  `location_name`     VARCHAR(200)             DEFAULT NULL,
  `location_address`  VARCHAR(255)             DEFAULT NULL,
  `is_virtual`        TINYINT(1)      NOT NULL DEFAULT 0,
  `virtual_meeting_url` VARCHAR(255)           DEFAULT NULL,
  `starts_at`         DATETIME        NOT NULL,
  `ends_at`           DATETIME        NOT NULL,
  `timezone`          VARCHAR(50)     NOT NULL DEFAULT 'Africa/Lagos',
  `featured_media_id` BIGINT UNSIGNED          DEFAULT NULL,
  `registration_required` TINYINT(1)  NOT NULL DEFAULT 0,
  `registration_url`  VARCHAR(255)             DEFAULT NULL COMMENT 'external link, used when not using the built-in RSVP table',
  `capacity`          INT UNSIGNED             DEFAULT NULL,
  `status`            ENUM('draft','published','cancelled','completed') NOT NULL DEFAULT 'draft',
  `created_by`        BIGINT UNSIGNED          DEFAULT NULL,
  `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`        DATETIME                 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_events_uuid` (`uuid`),
  UNIQUE KEY `uq_events_slug` (`slug`),
  KEY `idx_events_category` (`category_id`),
  KEY `idx_events_starts_at` (`starts_at`),
  KEY `idx_events_status` (`status`),
  CONSTRAINT `fk_events_category`
    FOREIGN KEY (`category_id`) REFERENCES `event_categories` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_events_featured_media`
    FOREIGN KEY (`featured_media_id`) REFERENCES `media` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_events_created_by`
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `chk_events_dates` CHECK (`ends_at` >= `starts_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- event_registrations
-- Simple built-in RSVP capture (name/email), independent of user accounts,
-- since most registrants will be public visitors, not logged-in users.
-- ---------------------------------------------------------------------------
CREATE TABLE `event_registrations` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id`      BIGINT UNSIGNED NOT NULL,
  `full_name`     VARCHAR(150)    NOT NULL,
  `email`         VARCHAR(190)    NOT NULL,
  `phone`         VARCHAR(30)              DEFAULT NULL,
  `organization_name` VARCHAR(180)         DEFAULT NULL,
  `status`        ENUM('registered','waitlisted','cancelled','attended') NOT NULL DEFAULT 'registered',
  `registered_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_event_registrations_event_email` (`event_id`, `email`),
  KEY `idx_event_registrations_event` (`event_id`),
  CONSTRAINT `fk_event_registrations_event`
    FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 9: NEWSLETTER
-- =============================================================================

CREATE TABLE `newsletter_subscribers` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`            CHAR(36)        NOT NULL DEFAULT (UUID()),
  `email`           VARCHAR(190)    NOT NULL,
  `full_name`       VARCHAR(150)             DEFAULT NULL,
  `status`          ENUM('pending','subscribed','unsubscribed','bounced') NOT NULL DEFAULT 'pending',
  `confirmation_token_hash` VARCHAR(255)     DEFAULT NULL COMMENT 'for double opt-in confirmation emails',
  `confirmed_at`    DATETIME                 DEFAULT NULL,
  `unsubscribed_at` DATETIME                 DEFAULT NULL,
  `source`          VARCHAR(100)             DEFAULT NULL COMMENT 'e.g. footer_form, event_signup',
  `ip_address`      VARCHAR(45)              DEFAULT NULL,
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`      DATETIME                 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_newsletter_subscribers_uuid` (`uuid`),
  UNIQUE KEY `uq_newsletter_subscribers_email` (`email`),
  KEY `idx_newsletter_subscribers_status` (`status`),
  KEY `idx_newsletter_subscribers_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `newsletter_campaigns` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `subject`       VARCHAR(255)    NOT NULL,
  `body_html`     LONGTEXT        NOT NULL,
  `status`        ENUM('draft','scheduled','sending','sent','failed') NOT NULL DEFAULT 'draft',
  `scheduled_at`  DATETIME                 DEFAULT NULL,
  `sent_at`       DATETIME                 DEFAULT NULL,
  `created_by`    BIGINT UNSIGNED          DEFAULT NULL,
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_newsletter_campaigns_created_by`
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `newsletter_campaign_sends` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id`    BIGINT UNSIGNED NOT NULL,
  `subscriber_id`  BIGINT UNSIGNED NOT NULL,
  `status`         ENUM('queued','sent','failed','opened','clicked') NOT NULL DEFAULT 'queued',
  `sent_at`        DATETIME                 DEFAULT NULL,
  `opened_at`      DATETIME                 DEFAULT NULL,
  `clicked_at`     DATETIME                 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_campaign_subscriber` (`campaign_id`, `subscriber_id`),
  CONSTRAINT `fk_campaign_sends_campaign`
    FOREIGN KEY (`campaign_id`) REFERENCES `newsletter_campaigns` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_campaign_sends_subscriber`
    FOREIGN KEY (`subscriber_id`) REFERENCES `newsletter_subscribers` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 10: CONTACT FORM SUBMISSIONS
-- =============================================================================

CREATE TABLE `contact_submissions` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`         CHAR(36)        NOT NULL DEFAULT (UUID()),
  `first_name`   VARCHAR(100)    NOT NULL,
  `last_name`    VARCHAR(100)    NOT NULL,
  `email`        VARCHAR(190)    NOT NULL,
  `phone`        VARCHAR(30)              DEFAULT NULL,
  `subject`      VARCHAR(200)             DEFAULT NULL,
  `message`      TEXT            NOT NULL,
  `audience_type` ENUM('professional','partner_donor','media','msme','regulator','general')
                                  NOT NULL DEFAULT 'general'
                                  COMMENT 'which "join the movement" CTA the enquiry came from',
  `status`       ENUM('new','in_progress','resolved','spam') NOT NULL DEFAULT 'new',
  `handled_by`   BIGINT UNSIGNED          DEFAULT NULL,
  `ip_address`   VARCHAR(45)              DEFAULT NULL,
  `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`   DATETIME                 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_contact_submissions_uuid` (`uuid`),
  KEY `idx_contact_submissions_status` (`status`),
  KEY `idx_contact_submissions_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_contact_submissions_handled_by`
    FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 11: SITE SETTINGS & GENERAL AUDIT LOG
-- =============================================================================

CREATE TABLE `site_settings` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key`   VARCHAR(100)    NOT NULL,
  `setting_value` TEXT                     DEFAULT NULL,
  `setting_group` VARCHAR(60)     NOT NULL DEFAULT 'general' COMMENT 'e.g. general, seo, social, contact',
  `updated_by`    BIGINT UNSIGNED          DEFAULT NULL,
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`    DATETIME                 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_site_settings_key` (`setting_key`),
  KEY `idx_site_settings_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_site_settings_updated_by`
    FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- audit_logs
-- Generic CRUD trail across CMS entities (who changed what, when), separate
-- from login_audit_logs (authentication events) and resource_download_logs
-- (file access events).
-- ---------------------------------------------------------------------------
CREATE TABLE `audit_logs` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`      BIGINT UNSIGNED          DEFAULT NULL,
  `action`       VARCHAR(60)     NOT NULL COMMENT 'created, updated, deleted, published, restored, ...',
  `entity_type`  VARCHAR(80)     NOT NULL COMMENT 'e.g. pages, posts, resources, events',
  `entity_id`    BIGINT UNSIGNED NOT NULL,
  `changes_json` JSON                     DEFAULT NULL COMMENT 'before/after diff, for traceability',
  `ip_address`   VARCHAR(45)              DEFAULT NULL,
  `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_logs_user` (`user_id`),
  KEY `idx_audit_logs_entity` (`entity_type`, `entity_id`),
  KEY `idx_audit_logs_created` (`created_at`),
  CONSTRAINT `fk_audit_logs_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 11b: INTERNAL MESSAGING — in-app inbox between staff and stakeholders
-- =============================================================================

-- ---------------------------------------------------------------------------
-- messages
-- A simple user-to-user inbox message, recorded in the database so staff can
-- message any logged-in user (admin/editor/stakeholder) and the recipient
-- sees it in an in-app inbox. sender_id is nullable so a sender account can
-- be deleted later without losing the message; recipient_id is required and
-- cascades on delete since a message has no purpose without its recipient.
-- ---------------------------------------------------------------------------
CREATE TABLE `messages` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sender_id`    BIGINT UNSIGNED          DEFAULT NULL COMMENT 'NULL if the sending account was later deleted',
  `recipient_id` BIGINT UNSIGNED NOT NULL,
  `subject`      VARCHAR(190)    NOT NULL,
  `body`         TEXT            NOT NULL,
  `read_at`      DATETIME                 DEFAULT NULL,
  `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at`   DATETIME                 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_messages_recipient_unread` (`recipient_id`, `read_at`),
  KEY `idx_messages_sender` (`sender_id`),
  KEY `idx_messages_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_messages_sender`
    FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_messages_recipient`
    FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 12: SEED DATA — baseline roles & permissions
-- =============================================================================

INSERT INTO `roles` (`name`, `display_name`, `description`, `is_system`) VALUES
  ('super_admin',           'Super Administrator',   'Full system access, including user management', 1),
  ('admin',                 'Administrator',         'Manage all CMS content', 1),
  ('editor',                'Editor',                'Create and edit content, cannot manage users', 1),
  ('stakeholder_insurer',   'Stakeholder - Insurer',  'Insurer representative with document portal access', 1),
  ('stakeholder_regulator', 'Stakeholder - Regulator','Regulator representative with document portal access', 1),
  ('stakeholder_partner',   'Stakeholder - Partner',  'Partner/donor representative with document portal access', 1);

INSERT INTO `permissions` (`name`, `display_name`, `module`) VALUES
  ('pages.view',       'View pages',               'pages'),
  ('pages.create',     'Create pages',             'pages'),
  ('pages.edit',       'Edit pages',               'pages'),
  ('pages.publish',    'Publish pages',            'pages'),
  ('pages.delete',     'Delete pages',             'pages'),
  ('posts.manage',     'Manage news/updates',      'posts'),
  ('resources.upload', 'Upload resources',         'resources'),
  ('resources.manage', 'Manage resources library', 'resources'),
  ('events.manage',    'Manage events calendar',   'events'),
  ('team.manage',      'Manage team members',      'team'),
  ('partners.manage',  'Manage partners',          'partners'),
  ('newsletter.manage','Manage newsletter',        'newsletter'),
  ('users.manage',     'Manage users & roles',     'users'),
  ('settings.manage',  'Manage site settings',     'settings');

-- super_admin gets every permission
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT (SELECT `id` FROM `roles` WHERE `name` = 'super_admin'), `id` FROM `permissions`;

-- admin gets everything except user management
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT (SELECT `id` FROM `roles` WHERE `name` = 'admin'), `id`
FROM `permissions` WHERE `name` <> 'users.manage';

-- editor: content creation/editing only, no publish/delete/user/settings rights
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT (SELECT `id` FROM `roles` WHERE `name` = 'editor'), `id`
FROM `permissions`
WHERE `name` IN ('pages.view','pages.create','pages.edit','posts.manage','resources.upload','events.manage');

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- END OF SCHEMA
-- =============================================================================
