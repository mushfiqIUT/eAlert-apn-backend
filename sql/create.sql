CREATE DATABASE
  elitalerts
DEFAULT CHARACTER SET
  utf8
DEFAULT COLLATE
  utf8_general_ci
;

CREATE USER
  'elitalerts'@'localhost'
IDENTIFIED BY
  '45xr61ElitAlerts'
;

GRANT ALL ON
  elitalerts.*
TO
  'elitalerts'@'localhost'
;

USE elitalerts;

CREATE TABLE IF NOT EXISTS `devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(255) NOT NULL,
  `is_enabled` tinyint(4) NOT NULL DEFAULT '1',
  `counter` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_token` (`token`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;