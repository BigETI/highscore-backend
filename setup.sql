-- -----------------------------------------------------
-- Schema highscore_backend
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `highscore_backend` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
USE `highscore_backend` ;

-- -----------------------------------------------------
-- Table `highscore_backend`.`users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `highscore_backend`.`users` (
  `uuid` VARCHAR(36) NOT NULL,
  `isBanned` TINYINT NOT NULL DEFAULT 0,
  `creationDateTime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uuid`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `highscore_backend`.`highscores`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `highscore_backend`.`highscores` (
  `uuid` VARCHAR(36) NOT NULL,
  `userUUID` VARCHAR(36) NOT NULL,
  `score` BIGINT UNSIGNED NOT NULL,
  `tries` INT UNSIGNED NOT NULL,
  `level` INT UNSIGNED NOT NULL,
  `name` VARCHAR(32) NOT NULL,
  `creationDateTime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uuid`, `userUUID`),
  INDEX `fk_highscores_users1_idx` (`userUUID` ASC),
  CONSTRAINT `fk_highscores_users1`
    FOREIGN KEY (`userUUID`)
    REFERENCES `highscore_backend`.`users` (`uuid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `highscore_backend`.`bans`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `highscore_backend`.`bans` (
  `uuid` INT NOT NULL,
  `ip` VARCHAR(48) NOT NULL,
  `reason` TINYTEXT NOT NULL,
  `creationDateTime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uuid`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `highscore_backend`.`infractions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `highscore_backend`.`infractions` (
  `uuid` VARCHAR(36) NOT NULL,
  `userUUID` VARCHAR(36) NOT NULL,
  `points` INT UNSIGNED NOT NULL,
  `reason` TINYTEXT NOT NULL,
  `creationDateTime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uuid`, `userUUID`),
  INDEX `fk_infractions_users1_idx` (`userUUID` ASC),
  CONSTRAINT `fk_infractions_users1`
    FOREIGN KEY (`userUUID`)
    REFERENCES `highscore_backend`.`users` (`uuid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `highscore_backend`.`badWords`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `highscore_backend`.`badWords` (
  `uuid` VARCHAR(36) NOT NULL,
  `badWord` VARCHAR(32) NOT NULL,
  `replaceWith` VARCHAR(32) NOT NULL,
  `creationDateTime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uuid`),
  UNIQUE INDEX `badWord_UNIQUE` (`badWord` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `highscore_backend`.`apps`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `highscore_backend`.`apps` (
  `uuid` VARCHAR(36) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  `secret` VARCHAR(64) NOT NULL,
  `description` TINYTEXT NOT NULL,
  `creationDateTime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uuid`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC),
  UNIQUE INDEX `secret_UNIQUE` (`secret` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `highscore_backend`.`appPrivileges`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `highscore_backend`.`appPrivileges` (
  `uuid` VARCHAR(36) NOT NULL,
  `appUUID` VARCHAR(36) NOT NULL,
  `privilege` VARCHAR(64) NOT NULL,
  `value` INT NOT NULL,
  `creationDateTime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uuid`, `appUUID`),
  UNIQUE INDEX `privilege_UNIQUE` (`privilege` ASC),
  INDEX `fk_appPrivileges_apps1_idx` (`appUUID` ASC),
  CONSTRAINT `fk_appPrivileges_apps1`
    FOREIGN KEY (`appUUID`)
    REFERENCES `highscore_backend`.`apps` (`uuid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `highscore_backend`.`appBans`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `highscore_backend`.`appBans` (
  `uuid` VARCHAR(36) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  `reason` TINYTEXT NOT NULL,
  `creationDateTime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uuid`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `highscore_backend`.`activities`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `highscore_backend`.`activities` (
  `uuid` VARCHAR(36) NOT NULL,
  `appName` VARCHAR(64) NOT NULL,
  `ip` VARCHAR(46) NOT NULL,
  `request` TINYTEXT NOT NULL,
  `creationDateTime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uuid`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `highscore_backend`.`motds`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `highscore_backend`.`motds` (
  `uuid` VARCHAR(36) NOT NULL,
  `motd` TEXT NOT NULL,
  `creationDateTime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uuid`))
ENGINE = InnoDB;