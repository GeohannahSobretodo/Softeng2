USE `crud`;

-- 1. Create the station master list.
CREATE TABLE IF NOT EXISTS `stations` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `station_code` VARCHAR(30) NOT NULL,
    `station_name` VARCHAR(100) NOT NULL,
    `department` ENUM('Kitchen','Service','Other') NOT NULL DEFAULT 'Other',
    `description` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_stations_code` (`station_code`),
    UNIQUE KEY `uq_stations_name_department` (`station_name`, `department`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Add the stations currently reported by the team.
INSERT IGNORE INTO `stations` (`station_code`, `station_name`, `department`) VALUES
('PREPPING', 'Prepping', 'Kitchen'),
('LINEAR', 'Linear', 'Kitchen'),
('ASSEMBLER', 'Assembler', 'Kitchen'),
('EXPERT', 'Expert', 'Kitchen'),
('GRILL', 'Grill', 'Kitchen'),
('DT_OT', 'DT OT', 'Service'),
('DT_RUNNER', 'DT Runner', 'Service'),
('DT_PRESENTER', 'DT Presenter', 'Service'),
('FC_OT', 'FC OT', 'Service'),
('FT_RUNNER', 'FT Runner', 'Service'),
('MDC', 'MDC', 'Service'),
('MDR', 'MDR', 'Service'),
('LOBBY', 'Lobby', 'Service'),
('MAINTENANCE', 'Maintenance', 'Other');

-- 3. Map crew members to stations they are trained to work at.
CREATE TABLE IF NOT EXISTS `crew_stations` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `crew_id` INT(11) NOT NULL,
    `station_id` INT(11) NOT NULL,
    `is_trained` TINYINT(1) NOT NULL DEFAULT 1,
    `trained_at` DATE DEFAULT NULL,
    `notes` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_crew_station` (`crew_id`, `station_id`),
    KEY `idx_crew_stations_station` (`station_id`),
    CONSTRAINT `fk_crew_stations_crew`
        FOREIGN KEY (`crew_id`) REFERENCES `crew_members` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_crew_stations_station`
        FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Add station assignment and break metadata to schedules.
ALTER TABLE `schedules`
    MODIFY COLUMN `shift_type`
        ENUM('GY','Morning','Mid','Closing')
        NOT NULL DEFAULT 'Morning',
    ADD COLUMN IF NOT EXISTS `station_id` INT(11) DEFAULT NULL AFTER `crew_id`,
    ADD COLUMN IF NOT EXISTS `break_minutes` SMALLINT UNSIGNED NOT NULL DEFAULT 20 AFTER `end_time`,
    ADD COLUMN IF NOT EXISTS `published_at` DATETIME DEFAULT NULL AFTER `status`,
    ADD COLUMN IF NOT EXISTS `schedule_notes` VARCHAR(500) DEFAULT NULL AFTER `published_at`,
    ADD KEY IF NOT EXISTS `idx_schedules_station_date` (`station_id`, `shift_date`),
    ADD CONSTRAINT `fk_schedules_station`
        FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL;

-- 5. Allow availability to use the new shift categories.
ALTER TABLE `crew_availability`
    MODIFY COLUMN `preferred_shift`
        ENUM('GY','Morning','Mid','Closing',)
        NOT NULL DEFAULT 'Any',
    ADD COLUMN IF NOT EXISTS `available_start_time` TIME DEFAULT NULL AFTER `preferred_shift`,
    ADD COLUMN IF NOT EXISTS `available_end_time` TIME DEFAULT NULL AFTER `available_start_time`;

-- 6. Additional rules, without deleting existing rules.
INSERT INTO `scheduling_rules`
    (`rule_name`, `rule_type`, `rule_value`, `description`, `is_active`)
SELECT 'maximum_shift_hours', 'hours', '8',
       'Maximum scheduled duty duration in hours, based on the process information supplied by the team.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM `scheduling_rules` WHERE `rule_name` = 'maximum_shift_hours'
);

INSERT INTO `scheduling_rules`
    (`rule_name`, `rule_type`, `rule_value`, `description`, `is_active`)
SELECT 'minimum_days_off_per_week', 'days', '1',
       'Minimum one day off per week, based on the process information supplied by the team.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM `scheduling_rules` WHERE `rule_name` = 'minimum_days_off_per_week'
);

INSERT INTO `scheduling_rules`
    (`rule_name`, `rule_type`, `rule_value`, `description`, `is_active`)
SELECT 'standard_break_minutes', 'minutes', '20',
       'Reported usual break duration; confirm store policy before relying on this value.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM `scheduling_rules` WHERE `rule_name` = 'standard_break_minutes'
);

-- 7. Store staffing targets by day group, shift, and department.
CREATE TABLE IF NOT EXISTS `staffing_requirements` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `day_group` ENUM('Monday-Thursday','Friday-Sunday','All Days') NOT NULL DEFAULT 'All Days',
    `shift_type` ENUM('GY','Morning','Mid','Closing','Any') NOT NULL DEFAULT 'Any',
    `department` ENUM('Kitchen','Service','Other','Total') NOT NULL,
    `required_min` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `required_max` SMALLINT UNSIGNED DEFAULT NULL,
    `notes` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_staffing_requirement` (`day_group`, `shift_type`, `department`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `staffing_requirements`
    (`day_group`, `shift_type`, `department`, `required_min`, `required_max`, `notes`)
VALUES
    ('Monday-Thursday', 'Any', 'Kitchen', 4, 4, 'Reported regular staffing requirement.'),
    ('Monday-Thursday', 'Any', 'Service', 7, 7, 'Reported service total includes one Lobby crew member.'),
    ('Monday-Thursday', 'Any', 'Total', 11, 11, 'Reported regular total.'),
    ('Friday-Sunday', 'Any', 'Kitchen', 5, 5, 'Reported regular staffing requirement.'),
    ('Friday-Sunday', 'Any', 'Service', 7, 8, 'Reported 6–7 Service plus one Lobby; confirm whether Lobby is counted separately in the source totals.'),
    ('Friday-Sunday', 'Any', 'Total', 12, 13, 'Reported regular total; confirm department counting before using for automated validation.')
ON DUPLICATE KEY UPDATE
    `required_min` = VALUES(`required_min`),
    `required_max` = VALUES(`required_max`),
    `notes` = VALUES(`notes`),
    `is_active` = 1;

-- 8. Store crew leave / day-off / schedule-change requests for manager approval.
CREATE TABLE IF NOT EXISTS `schedule_requests` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `crew_id` INT(11) NOT NULL,
    `request_type` ENUM('Leave','Day Off','Schedule Change','Availability Change','Other') NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE DEFAULT NULL,
    `requested_start_time` TIME DEFAULT NULL,
    `requested_end_time` TIME DEFAULT NULL,
    `reason` TEXT DEFAULT NULL,
    `status` ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
    `reviewed_by` INT(11) DEFAULT NULL,
    `reviewed_at` DATETIME DEFAULT NULL,
    `manager_notes` VARCHAR(500) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_schedule_requests_crew_dates` (`crew_id`, `start_date`, `end_date`),
    KEY `idx_schedule_requests_status` (`status`),
    CONSTRAINT `fk_schedule_requests_crew`
        FOREIGN KEY (`crew_id`) REFERENCES `crew_members` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
