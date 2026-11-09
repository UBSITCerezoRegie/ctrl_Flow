-- Ctrl+Flow — Barangay Drainage Blockage Report System
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','crew','citizen') NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `barangay_assigned` varchar(100) DEFAULT NULL,
  `is_verified` tinyint(4) DEFAULT 0,
  `verification_token` varchar(150) DEFAULT NULL,
  `password_reset_required` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `blockage_reports` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `report_code` varchar(50) NOT NULL,
  `reported_by_user_id` int(11) DEFAULT NULL,
  `barangay` varchar(100) NOT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `material_type` enum('garbage_plastic','silt_mud_leaves','grease_fatberg','unknown_other') NOT NULL DEFAULT 'unknown_other',
  `severity` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `is_spilling` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `status` enum('active','under_review','cleared') NOT NULL DEFAULT 'active',
  `assigned_crew_id` int(11) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `status_notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`report_id`),
  UNIQUE KEY `report_code` (`report_code`),
  KEY `barangay` (`barangay`),
  KEY `status` (`status`),
  KEY `reported_by_user_id` (`reported_by_user_id`),
  KEY `assigned_crew_id` (`assigned_crew_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `escalations` (
  `escalation_id` int(11) NOT NULL AUTO_INCREMENT,
  `escalation_code` varchar(50) DEFAULT NULL,
  `report_id` int(11) NOT NULL,
  `escalation_date` date DEFAULT NULL,
  `reporting_barangay` varchar(150) DEFAULT NULL,
  `reporting_tel` varchar(50) DEFAULT NULL,
  `reporting_email` varchar(150) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `receiving_office` varchar(150) DEFAULT NULL,
  `receiving_barangay` varchar(255) DEFAULT NULL,
  `receiving_officer` varchar(150) DEFAULT NULL,
  `date_received` date DEFAULT NULL,
  `action_taken` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `escalation_status` enum('pending','received','completed') DEFAULT 'pending',
  `received_by` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`escalation_id`),
  KEY `report_id` (`report_id`),
  KEY `receiving_barangay` (`receiving_barangay`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `report_id` int(11) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `scheduled_at` datetime DEFAULT NULL,
  `is_sent` tinyint(4) DEFAULT 0,
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  KEY `report_id` (`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `import_logs` (
  `import_id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(200) DEFAULT NULL,
  `rows_imported` int(11) DEFAULT NULL,
  `imported_by` int(11) DEFAULT NULL,
  `imported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`import_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `blockage_reports`
  ADD CONSTRAINT `fk_reports_user` FOREIGN KEY (`reported_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_reports_crew` FOREIGN KEY (`assigned_crew_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

ALTER TABLE `escalations`
  ADD CONSTRAINT `fk_escalations_report` FOREIGN KEY (`report_id`) REFERENCES `blockage_reports` (`report_id`) ON DELETE CASCADE;

ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notifications_report` FOREIGN KEY (`report_id`) REFERENCES `blockage_reports` (`report_id`) ON DELETE SET NULL;

SET FOREIGN_KEY_CHECKS=1;
