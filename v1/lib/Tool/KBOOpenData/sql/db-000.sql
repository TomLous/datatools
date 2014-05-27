SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `KBOOpenData_activity`
-- ----------------------------
-- DROP TABLE IF EXISTS `KBOOpenData_activity`;
CREATE TABLE IF NOT EXISTS `KBOOpenData_activity` (
  `EntityNumber` varchar(13) CHARACTER SET utf8 NOT NULL COMMENT '9999.999.999 of 9.999.999.999',
  `ActivityGroup` varchar(6) CHARACTER SET utf8 NOT NULL COMMENT '(6)X',
  `NaceVersion` enum('2003','2008') CHARACTER SET utf8 NOT NULL,
  `NaceCode` varchar(9) CHARACTER SET utf8 NOT NULL COMMENT '(5)9 of (7)9',
  `Classification` char(4) CHARACTER SET utf8 NOT NULL COMMENT 'XXXX'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `KBOOpenData_address`
-- ----------------------------
-- DROP TABLE IF EXISTS `KBOOpenData_address`;
CREATE TABLE IF NOT EXISTS `KBOOpenData_address` (
  `EntityNumber` varchar(13) CHARACTER SET utf8 NOT NULL COMMENT '9999.999.999 of 9.999.999.999',
  `TypeOfAddress` char(4) CHARACTER SET utf8 NOT NULL COMMENT 'XXXX',
  `CountryNL` varchar(100) CHARACTER SET utf8 DEFAULT NULL COMMENT '100(X)',
  `CountryFR` varchar(100) CHARACTER SET utf8 DEFAULT NULL COMMENT '100(X)',
  `Zipcode` varchar(20) CHARACTER SET utf8 DEFAULT NULL COMMENT '20(X)',
  `MunicipalityNL` varchar(200) CHARACTER SET utf8 DEFAULT NULL COMMENT '200(X)',
  `MunicipalityFR` varchar(200) CHARACTER SET utf8 DEFAULT NULL COMMENT '200(X)',
  `StreetNL` varchar(200) CHARACTER SET utf8 DEFAULT NULL COMMENT '200(X)',
  `StreetFR` varchar(200) CHARACTER SET utf8 DEFAULT NULL COMMENT '200(X)',
  `HouseNumber` varchar(22) CHARACTER SET utf8 DEFAULT NULL COMMENT '22(X)',
  `Box` varchar(20) CHARACTER SET utf8 DEFAULT NULL COMMENT '20(X)',
  `ExtraAddressInfo` varchar(80) CHARACTER SET utf8 DEFAULT NULL COMMENT '80(X)',
  `DateStrikingOff` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `KBOOpenData_code`
-- ----------------------------
-- DROP TABLE IF EXISTS `KBOOpenData_code`;
CREATE TABLE IF NOT EXISTS `KBOOpenData_code` (
  `Category` varchar(255) CHARACTER SET utf8 NOT NULL,
  `Code` varchar(255) CHARACTER SET utf8 NOT NULL,
  `Language` enum('DE','EN','FR','NL') CHARACTER SET utf8 NOT NULL,
  `Description` text CHARACTER SET utf8 NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `KBOOpenData_contact`
-- ----------------------------
-- DROP TABLE IF EXISTS `KBOOpenData_contact`;
CREATE TABLE IF NOT EXISTS `KBOOpenData_contact` (
  `EntityNumber` varchar(13) NOT NULL COMMENT '9999.999.999 of 9.999.999.999',
  `EntityContact` varchar(3) NOT NULL COMMENT '(3)X',
  `ContactType` varchar(5) NOT NULL COMMENT '5(X)',
  `Value` varchar(254) NOT NULL COMMENT '(254)X'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `KBOOpenData_denomination`
-- ----------------------------
-- DROP TABLE IF EXISTS `KBOOpenData_denomination`;
CREATE TABLE IF NOT EXISTS `KBOOpenData_denomination` (
  `EntityNumber` varchar(13) CHARACTER SET utf8 NOT NULL COMMENT '9999.999.999 of 9.999.999.999',
  `Language` char(1) CHARACTER SET utf8 NOT NULL COMMENT 'X',
  `TypeOfDenomination` char(3) CHARACTER SET utf8 NOT NULL COMMENT 'XXX',
  `Denomination` varchar(320) CHARACTER SET utf8 NOT NULL COMMENT '(320)X'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `KBOOpenData_enterprise`
-- ----------------------------
-- DROP TABLE IF EXISTS `KBOOpenData_enterprise`;
CREATE TABLE IF NOT EXISTS `KBOOpenData_enterprise` (
  `EnterpiseNumber` varchar(12) CHARACTER SET utf8 NOT NULL COMMENT '9999.999.999',
  `Status` char(2) CHARACTER SET utf8 NOT NULL COMMENT 'XX',
  `JuridicalSituation` char(3) CHARACTER SET utf8 NOT NULL COMMENT 'XXX',
  `TypeOfEnterprise` char(1) CHARACTER SET utf8 NOT NULL COMMENT 'X',
  `JuridicalForm` char(3) CHARACTER SET utf8 DEFAULT NULL COMMENT 'XXX',
  `StartDate` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `KBOOpenData_establishment`
-- ----------------------------
-- DROP TABLE IF EXISTS `KBOOpenData_establishment`;
CREATE TABLE IF NOT EXISTS `KBOOpenData_establishment` (
  `EstablishmentNumber` varchar(13) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '9.999.999.999',
  `StartDate` date NOT NULL,
  `EnterpiseNumber` varchar(12) CHARACTER SET utf8 NOT NULL COMMENT '9999.999.999'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `KBOOpenData_meta`
-- ----------------------------
-- DROP TABLE IF EXISTS `KBOOpenData_meta`;
CREATE TABLE IF NOT EXISTS `KBOOpenData_meta` (
  `Variable` varchar(255) CHARACTER SET utf8 NOT NULL,
  `Value` varchar(255) CHARACTER SET utf8 DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

