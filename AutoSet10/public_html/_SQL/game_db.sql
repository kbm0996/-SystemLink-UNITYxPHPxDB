CREATE SCHEMA `game_schema` DEFAULT CHARACTER SET utf8;

USE game_schema;

CREATE TABLE `account` (
	`accountno` bigint(20) NOT NULL AUTO_INCREMENT,
	`id` char(30) NOT NULL UNIQUE,
	`password` char(70) NOT NULL,
	 PRIMARY KEY (`accountno`)
) ENGINE=InnoDB;

CREATE TABLE `login` (
	`accountno` bigint(20) NOT NULL, 
	`time` bigint(20) NOT NULL,
	`ip` char(30) NOT NULL,
	`count` bigint(20) NOT NULL,
	 PRIMARY KEY (`accountno`)
) ENGINE=InnoDB;

CREATE TABLE `clearstage` (
  `accountno` bigint(20) NOT NULL,
  `stageid` int(11) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE `data_levelup` (
  `level` int(11) NOT NULL,
  `exp` int(11) NOT NULL,
   PRIMARY KEY (`level`)
) ENGINE=InnoDB;

CREATE TABLE `data_stage` (
  `stageid` int(11) NOT NULL,
  `clearexp` int(11) NOT NULL,
  PRIMARY KEY (`stageid`)
) ENGINE=InnoDB;

CREATE TABLE `player` (
  `accountno` bigint(20) NOT NULL AUTO_INCREMENT,
  `level` int(11) NOT NULL DEFAULT '1',
  `exp` bigint(20) NOT NULL DEFAULT '0',
   PRIMARY KEY (`accountno`)
) ENGINE=InnoDB;

CREATE TABLE `session` (
  `accountno` bigint(20) NOT NULL,
  `session` char(32) NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`accountno`)
) ENGINE=InnoDB;

INSERT INTO `data_levelup` (`level`, `exp`) VALUES ('1', '50');
INSERT INTO `data_levelup` (`level`, `exp`) VALUES ('2', '100');
INSERT INTO `data_levelup` (`level`, `exp`) VALUES ('3', '150');
INSERT INTO `data_levelup` (`level`, `exp`) VALUES ('4', '250');
INSERT INTO `data_levelup` (`level`, `exp`) VALUES ('5', '370');

INSERT INTO `data_stage` (`stageid`, `clearexp`) VALUES ('1', '40');
INSERT INTO `data_stage` (`stageid`, `clearexp`) VALUES ('2', '50');
INSERT INTO `data_stage` (`stageid`, `clearexp`) VALUES ('3', '60');
INSERT INTO `data_stage` (`stageid`, `clearexp`) VALUES ('4', '70');
INSERT INTO `data_stage` (`stageid`, `clearexp`) VALUES ('5', '80');
INSERT INTO `data_stage` (`stageid`, `clearexp`) VALUES ('6', '90');
INSERT INTO `data_stage` (`stageid`, `clearexp`) VALUES ('7', '100');
INSERT INTO `data_stage` (`stageid`, `clearexp`) VALUES ('8', '100');
INSERT INTO `data_stage` (`stageid`, `clearexp`) VALUES ('9', '100');
INSERT INTO `data_stage` (`stageid`, `clearexp`) VALUES ('10', '100');
INSERT INTO `data_stage` (`stageid`, `clearexp`) VALUES ('11', '100');

