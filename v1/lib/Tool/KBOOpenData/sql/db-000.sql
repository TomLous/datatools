SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `KBOOpenData_activity`
-- ----------------------------
-- DROP TABLE IF EXISTS `KBOOpenData_activity`;
CREATE TABLE IF NOT EXISTS `KBOOpenData_activity` (
  `EntityNumber` varchar(13) NOT NULL COMMENT '9999.999.999 of 9.999.999.999',
  `ActivityGroup` varchar(6) NOT NULL COMMENT '(6)X',
  `NaceVersion` enum('2003','2008') NOT NULL,
  `NaceCode` varchar(9) NOT NULL COMMENT '(5)9 of (7)9',
  `Classification` char(4) NOT NULL COMMENT 'XXXX',
  PRIMARY KEY (`EntityNumber`,`ActivityGroup`,`NaceVersion`,`NaceCode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `KBOOpenData_address`
-- ----------------------------
-- DROP TABLE IF EXISTS `KBOOpenData_address`;
CREATE TABLE IF NOT EXISTS `KBOOpenData_address` (
  `EntityNumber` varchar(13) NOT NULL COMMENT '9999.999.999 of 9.999.999.999',
  `TypeOfAddress` char(4) NOT NULL COMMENT 'XXXX',
  `CountryNL` varchar(100) DEFAULT NULL COMMENT '100(X)',
  `CountryFR` varchar(100) DEFAULT NULL COMMENT '100(X)',
  `Zipcode` varchar(20) DEFAULT NULL COMMENT '20(X)',
  `MunicipalityNL` varchar(200) DEFAULT NULL COMMENT '200(X)',
  `MunicipalityFR` varchar(200) DEFAULT NULL COMMENT '200(X)',
  `StreetNL` varchar(200) DEFAULT NULL COMMENT '200(X)',
  `StreetFR` varchar(200) DEFAULT NULL COMMENT '200(X)',
  `HouseNumber` varchar(22) DEFAULT NULL COMMENT '22(X)',
  `Box` varchar(20) DEFAULT NULL COMMENT '20(X)',
  `ExtraAddressInfo` varchar(80) DEFAULT NULL COMMENT '80(X)',
  `DateStrikingOff` char(10) NOT NULL COMMENT 'XX-XX-XXXX',
  PRIMARY KEY (`EntityNumber`,`TypeOfAddress`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `KBOOpenData_code`
-- ----------------------------
-- DROP TABLE IF EXISTS `KBOOpenData_code`;
CREATE TABLE IF NOT EXISTS `KBOOpenData_code` (
  `Category` varchar(255) NOT NULL,
  `Code` varchar(255) NOT NULL,
  `Language` enum('DE','EN','FR','NL') NOT NULL,
  `Description` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `KBOOpenData_contact`
-- ----------------------------
-- DROP TABLE IF EXISTS `KBOOpenData_contact`;
CREATE TABLE IF NOT EXISTS `KBOOpenData_contact` (
  `EntityNumber` varchar(13) NOT NULL COMMENT '9999.999.999 of 9.999.999.999',
  `EntityContact` varchar(3) NOT NULL COMMENT '(3)X',
  `ContactType` varchar(5) NOT NULL COMMENT '5(X)',
  `Value` varchar(254) NOT NULL COMMENT '(254)X',
  PRIMARY KEY (`EntityNumber`,`EntityContact`,`ContactType`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `KBOOpenData_denomination`
-- ----------------------------
-- DROP TABLE IF EXISTS `KBOOpenData_denomination`;
CREATE TABLE IF NOT EXISTS `KBOOpenData_denomination` (
  `EntityNumber` varchar(13) NOT NULL COMMENT '9999.999.999 of 9.999.999.999',
  `Language` char(1) NOT NULL COMMENT 'X',
  `TypeOfDenomination` char(3) NOT NULL COMMENT 'XXX',
  `Denomination` varchar(320) NOT NULL COMMENT '(320)X',
  PRIMARY KEY (`EntityNumber`,`TypeOfDenomination`,`Language`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `KBOOpenData_enterprise`
-- ----------------------------
-- DROP TABLE IF EXISTS `KBOOpenData_enterprise`;
CREATE TABLE IF NOT EXISTS `KBOOpenData_enterprise` (
  `EnterpriseNumber` varchar(12) NOT NULL COMMENT '9999.999.999',
  `Status` char(2) NOT NULL COMMENT 'XX',
  `JuridicalSituation` char(3) NOT NULL COMMENT 'XXX',
  `TypeOfEnterprise` char(1) NOT NULL COMMENT 'X',
  `JuridicalForm` char(3) DEFAULT NULL COMMENT 'XXX',
  `StartDate` char(10) NOT NULL COMMENT 'XX-XX-XXXX',
  PRIMARY KEY (`EnterpriseNumber`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `KBOOpenData_establishment`
-- ----------------------------
-- DROP TABLE IF EXISTS `KBOOpenData_establishment`;
CREATE TABLE IF NOT EXISTS `KBOOpenData_establishment` (
  `EstablishmentNumber` varchar(13) NOT NULL DEFAULT '' COMMENT '9.999.999.999',
  `StartDate` char(10) NOT NULL COMMENT 'XX-XX-XXXX',
  `EnterpriseNumber` varchar(12) NOT NULL COMMENT '9999.999.999',
  PRIMARY KEY (`EstablishmentNumber`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `KBOOpenData_meta`
-- ----------------------------
-- DROP TABLE IF EXISTS `KBOOpenData_meta`;
CREATE TABLE IF NOT EXISTS `KBOOpenData_meta` (
  `Variable` varchar(255) NOT NULL,
  `Value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Variable`),
  UNIQUE KEY `metaUnique` (`Variable`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `KBOOpenData_mutationlog`
-- ----------------------------
-- DROP TABLE IF EXISTS `KBOOpenData_mutationlog`;
CREATE TABLE IF NOT EXISTS `KBOOpenData_mutationlog` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tableName` varchar(64) NOT NULL,
  `recordKey` varchar(32) NOT NULL,
  `data_old` text,
  `data_new` text,
  `action` enum('insert','update','delete') NOT NULL,
  `mutationTimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=ARCHIVE AUTO_INCREMENT=64756 DEFAULT CHARSET=utf8;

