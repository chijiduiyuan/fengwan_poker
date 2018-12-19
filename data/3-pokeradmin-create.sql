-- MySQL dump 10.13  Distrib 5.7.17, for Win64 (x86_64)
--
-- Host: 192.168.1.72    Database: gm
-- ------------------------------------------------------
-- Server version	5.7.18

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin`
--

CREATE DATABASE  IF NOT EXISTS `gm` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `gm`;

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `username` varchar(255) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `encrypt` varchar(255) NOT NULL COMMENT '加密串',
  `nickname` varchar(255) NOT NULL COMMENT '昵称',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像url',
  `purview` text COMMENT '权限',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT ' 0=锁定 1=正常',
  PRIMARY KEY (`aid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='管理员表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `game_version`
--

DROP TABLE IF EXISTS `game_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `game_version` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device` tinyint(4) NOT NULL DEFAULT '0' COMMENT '设备类型 1=安卓 2=ios 0=其他',
  `title` varchar(255) NOT NULL COMMENT '版本名称',
  `txt` text COMMENT '版本更新内容',
  `url` varchar(255) NOT NULL COMMENT '版本url',
  `vcode` int(11) NOT NULL DEFAULT '0' COMMENT '版本号',
  `isForceUpdate` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否强制更新 1=是0=否',
  `isHotfix` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否是热更新 1=是0=否',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=不发布 1=已发布',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `packageSize` bigint(11) NOT NULL DEFAULT '0' COMMENT '包大小 单位 b字节',
  PRIMARY KEY (`id`),
  UNIQUE KEY `vcode` (`vcode`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='版本更新表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pay_day`
--

DROP TABLE IF EXISTS `pay_day`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_day` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `time` int(10) unsigned NOT NULL COMMENT '时间',
  `dateTime` varchar(255) NOT NULL COMMENT '文本日期',
  `paypal` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'paypal',
  `apple` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'apple',
  `total` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '总计',
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='日流水统计';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pay_month`
--

DROP TABLE IF EXISTS `pay_month`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_month` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `time` int(10) unsigned NOT NULL COMMENT '时间',
  `dateTime` varchar(255) NOT NULL COMMENT '文本日期',
  `paypal` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'paypal',
  `apple` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'apple',
  `total` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '总计',
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='月流水统计';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-08-14 17:20:20
