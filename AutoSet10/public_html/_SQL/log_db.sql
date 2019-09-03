CREATE SCHEMA `log_schema` DEFAULT CHARACTER SET utf8;

USE log_schema;

CREATE TABLE `systemlog_template` (
	`no` BIGINT primary key not null auto_increment,
	`date` DATETIME not null,
	`accountno` VARCHAR(45) not null,
	`action` TEXT default null,
	`message` TEXT default null
);

CREATE TABLE `gamelog_template`(
	`no` BIGINT primary key not null auto_increment,
	`date` DATETIME not null,
	`accountno` VARCHAR(45) not null,
	`logtype` INT(11) not null,
	`logcode` INT(11) not null,
	`param1` INT(11) default '0',
	`param2` INT(11) default '0',
	`param3` INT(11) default '0',
	`param4` INT(11) default '0',
	`paramstring` TEXT
);

CREATE TABLE `profilinglog_template`(
	`no` BIGINT primary key not null auto_increment,
	`date` DATETIME not null,
	`ip` VARCHAR(45) not null,
	`accountno` BIGINT not null,
	`action` VARCHAR(128) default null,
	`t_page` FLOAT not null,
	`t_mysql_conn` FLOAT not null,
	`t_mysql` FLOAT not null,
	`t_extapi` FLOAT not null,
	`t_log` FLOAT not null,
	`t_ru_u` FLOAT not null,
	`t_ru_s` FLOAT not null,
	`query` TEXT not null,
	`comment` TEXT not null
)default charset=utf8;