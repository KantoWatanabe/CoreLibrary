CREATE DATABASE test;
USE test;

CREATE USER test@localhost IDENTIFIED WITH mysql_native_password BY 'test';
GRANT ALL PRIVILEGES ON test.* to test@localhost;

CREATE TABLE IF NOT EXISTS `test` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `col1` INT(11) NOT NULL,
  `col2` VARCHAR(100) NOT NULL,
  `col3` TEXT DEFAULT NULL,
  `col4` TINYINT(1),
  `col5` FLOAT(5,2),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
