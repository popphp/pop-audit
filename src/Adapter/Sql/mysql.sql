CREATE TABLE `audit_log` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `user_id` int(16) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `model` varchar(255) NOT NULL,
  `model_id` int(16) NOT NULL,
  `action` varchar(255) NOT NULL,
  `old` longtext DEFAULT NULL,
  `new` longtext DEFAULT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `user_id` (`user_id`),
  INDEX `username` (`username`),
  INDEX `model` (`model`),
  INDEX `model_id` (`model_id`),
  INDEX `action` (`action`),
  INDEX `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
