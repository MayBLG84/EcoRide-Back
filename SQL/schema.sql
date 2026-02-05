-- ----------------------------
-- Schema do banco de dados
-- ----------------------------

SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE `role` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `name` VARCHAR(25) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `vehicle_brand` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `brand` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(255) NOT NULL,
  `nickname` VARCHAR(255) NOT NULL,
  `email` VARCHAR(180) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `telephone` VARCHAR(15) DEFAULT NULL,
  `birthday` DATETIME NOT NULL,
  `photo` LONGBLOB DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `credit` DOUBLE NOT NULL,
  `avg_rating` DOUBLE NOT NULL DEFAULT 0,
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`),
  UNIQUE KEY `UNIQ_NICKNAME` (`nickname`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `vehicle` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `vehicle_brand_id` INT NOT NULL,
  `model` VARCHAR(255) NOT NULL,
  `color` VARCHAR(255) NOT NULL,
  `registration` VARCHAR(15) NOT NULL,
  `first_rg_date` DATETIME NOT NULL,
  `electric` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `shared_vehicle` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `IDX_vehicle_brand` (`vehicle_brand_id`),
  CONSTRAINT `FK_vehicle_brand` FOREIGN KEY (`vehicle_brand_id`) REFERENCES `vehicle_brand` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ride_status` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `label` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ride` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `driver_id` INT NOT NULL,
  `ride_status_id` INT NOT NULL,
  `vehicle_id` INT NOT NULL,
  `origin_city` VARCHAR(60) NOT NULL,
  `pick_point` VARCHAR(255) NOT NULL,
  `departure_date` DATETIME NOT NULL,
  `departure_intended_time` TIME NOT NULL,
  `departure_real_time` TIME DEFAULT NULL,
  `destiny_city` VARCHAR(60) NOT NULL,
  `drop_point` VARCHAR(255) NOT NULL,
  `arrival_date` DATETIME NOT NULL,
  `arrival_estimated_time` TIME NOT NULL,
  `arrival_real_time` TIME DEFAULT NULL,
  `nb_places_offered` INT NOT NULL,
  `nb_places_available` INT NOT NULL,
  `price_person` DOUBLE NOT NULL,
  `smokers_allowed` TINYINT(1) NOT NULL,
  `animals_allowed` TINYINT(1) NOT NULL,
  `other_preferences` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `cancelled_at` DATETIME DEFAULT NULL,
  `estimated_duration` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `IDX_ride_driver` (`driver_id`),
  KEY `IDX_ride_status` (`ride_status_id`),
  KEY `IDX_ride_vehicle` (`vehicle_id`),
  CONSTRAINT `FK_ride_driver` FOREIGN KEY (`driver_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_ride_status` FOREIGN KEY (`ride_status_id`) REFERENCES `ride_status` (`id`),
  CONSTRAINT `FK_ride_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicle` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `evaluation_status` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `label` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `evaluation` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `ride_id` INT NOT NULL,
  `passenger_id` INT NOT NULL,
  `status_id` INT NOT NULL,
  `treated_by_id` INT DEFAULT NULL,
  `validation_passenger` TINYINT(1) NOT NULL,
  `rate` INT NOT NULL,
  `comment` LONGTEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `claimed_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `concluded_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_eval_ride` (`ride_id`),
  KEY `IDX_eval_passenger` (`passenger_id`),
  KEY `IDX_eval_status` (`status_id`),
  KEY `IDX_eval_treated` (`treated_by_id`),
  CONSTRAINT `FK_eval_ride` FOREIGN KEY (`ride_id`) REFERENCES `ride` (`id`),
  CONSTRAINT `FK_eval_passenger` FOREIGN KEY (`passenger_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_eval_status` FOREIGN KEY (`status_id`) REFERENCES `evaluation_status` (`id`),
  CONSTRAINT `FK_eval_treated` FOREIGN KEY (`treated_by_id`) REFERENCES `user` (`id`),
  UNIQUE KEY `uniq_ride_passenger_eval` (`ride_id`, `passenger_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_role` (
  `user_id` INT NOT NULL,
  `role_id` INT NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `IDX_user_role_user` (`user_id`),
  KEY `IDX_user_role_role` (`role_id`),
  CONSTRAINT `FK_user_role_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_user_role_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_vehicle` (
  `user_id` INT NOT NULL,
  `vehicle_id` INT NOT NULL,
  PRIMARY KEY (`user_id`,`vehicle_id`),
  KEY `IDX_user_vehicle_user` (`user_id`),
  KEY `IDX_user_vehicle_vehicle` (`vehicle_id`),
  CONSTRAINT `FK_user_vehicle_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_user_vehicle_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicle` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ride_passenger` (
  `ride_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  PRIMARY KEY (`ride_id`,`user_id`),
  KEY `IDX_ride_passenger_ride` (`ride_id`),
  KEY `IDX_ride_passenger_user` (`user_id`),
  CONSTRAINT `FK_ride_passenger_ride` FOREIGN KEY (`ride_id`) REFERENCES `ride` (`id`),
  CONSTRAINT `FK_ride_passenger_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_address` (
  `user_id` INT NOT NULL,
  `number` VARCHAR(6) NOT NULL,
  `street` VARCHAR(255) NOT NULL,
  `complement` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(60) NOT NULL,
  `zipcode` VARCHAR(10) NOT NULL,
  `country` VARCHAR(60) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `FK_user_address_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;