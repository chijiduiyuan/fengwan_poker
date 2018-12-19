-- MySQL dump 10.13  Distrib 5.7.17, for Win64 (x86_64)
--
-- Host: 192.168.1.72    Database: poker
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
-- Table structure for table `club`
--

CREATE DATABASE  IF NOT EXISTS `poker` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `poker`;

DROP TABLE IF EXISTS `club`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club` (
  `clubId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `title` varchar(255) NOT NULL COMMENT '俱乐部名称',
  `avatar` varchar(255) NOT NULL DEFAULT ' ' COMMENT '俱乐部头像的URL',
  `cid` int(10) unsigned NOT NULL COMMENT '国家id',
  `rb` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '俱乐部币',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '俱乐部等级',
  `subagentLimit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '可设置副代理数量上限',
  `memberLimit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '可加入会员数量上限',
  `luckyFlag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '可查看幸运玩家报表 0=否 1=是',
  `expir` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '等级到期时间',
  `intro` text NOT NULL COMMENT '简介公告',
  `checkip` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0=不检查 1=检查',
  `create_uid` int(11) NOT NULL DEFAULT '0' COMMENT '创建者uid',
  `create_nickname` varchar(255) DEFAULT NULL COMMENT '创建者昵称',
  `time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态 1=正常 0=解散',
  PRIMARY KEY (`clubId`)
) ENGINE=InnoDB AUTO_INCREMENT=10000 DEFAULT CHARSET=utf8 COMMENT='俱乐部';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_apply`
--

DROP TABLE IF EXISTS `club_apply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_apply` (
  `applyId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '申请id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `uid` int(10) unsigned NOT NULL COMMENT '玩家id',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态 1=申请中 2=同意 3=拒绝',
  `time` int(10) unsigned NOT NULL COMMENT '申请时间',
  PRIMARY KEY (`applyId`),
  KEY `clubId` (`clubId`,`status`),
  KEY `clubId_2` (`clubId`,`uid`,`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='申请加入俱乐部的玩家列表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_counter_record`
--

DROP TABLE IF EXISTS `club_counter_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_counter_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `uid_operator` int(10) unsigned NOT NULL COMMENT '操作者uid',
  `uid_target` int(10) unsigned NOT NULL COMMENT '被操作会员uid',
  `coin` int(10) unsigned NOT NULL COMMENT '收发筹码数量',
  `scale` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '服务费',
  `type` tinyint(3) unsigned NOT NULL COMMENT '类型 1=发送 2=回收',
  `time` int(10) unsigned NOT NULL COMMENT '收发时间',
  PRIMARY KEY (`id`),
  KEY `clubId` (`clubId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='柜台筹码收发记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_level_shop`
--

DROP TABLE IF EXISTS `club_level_shop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_level_shop` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `title` varchar(100) NOT NULL COMMENT '标题',
  `avatar` varchar(255) NOT NULL COMMENT '图标',
  `level` smallint(5) unsigned NOT NULL COMMENT '等级',
  `subagent_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '可设置副代理数',
  `member_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '可加入会员数',
  `lucky_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '查看幸运玩家 0=否 1=是',
  `time_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '有效期(天)',
  `price` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '价格',
  `orders` smallint(5) unsigned NOT NULL DEFAULT '99' COMMENT '排序',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态 0=禁用 1=启用',
  PRIMARY KEY (`id`),
  KEY `status` (`status`,`orders`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='俱乐部等级商店';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_member`
--

DROP TABLE IF EXISTS `club_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '所属俱乐部id',
  `uid` int(10) unsigned NOT NULL COMMENT '玩家uid',
  `coin` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '筹码(R币)',
  `note` varchar(255) NOT NULL DEFAULT ' ' COMMENT '玩家备注',
  `manage` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否管理 0=否 1=副代理 2=主代理',
  `purview` varchar(255) NOT NULL DEFAULT '[]' COMMENT '管理俱乐部的权限',
  `time` int(10) unsigned NOT NULL COMMENT '加入时间',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态 1=正常 2=删除',
  PRIMARY KEY (`id`),
  KEY `club_id` (`clubId`,`uid`) USING BTREE,
  KEY `club_id_2` (`clubId`,`status`,`manage`) USING BTREE,
  KEY `uid` (`uid`,`status`,`manage`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='俱乐部会员表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room`
--

DROP TABLE IF EXISTS `club_room`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room` (
  `roomId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `pw` varchar(255) DEFAULT NULL COMMENT '密码',
  `game` varchar(50) NOT NULL COMMENT '房间类型 dzPoker=德州，cowWater=牛加水',
  `opTimeout` int(10) unsigned NOT NULL COMMENT '操作时间 单位秒',
  `costScale` float unsigned NOT NULL COMMENT '抽成比例',
  `roomTime` int(10) unsigned NOT NULL COMMENT '牌局时间 单位秒',
  `playerNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '玩家数 5人房,7人房 牛加水专有',
  `blindBet` int(10) unsigned NOT NULL COMMENT '盲注/底注',
  `minBet` int(10) unsigned NOT NULL COMMENT '筹码下限',
  `maxBet` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '筹码上限',
  `enableBuy` tinyint(3) unsigned NOT NULL COMMENT '授权买入 0=不允许 1=允许',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否开局 0=否 1=是',
  `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开局时间',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`roomId`),
  KEY `startTime` (`clubId`,`startTime`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='私人俱乐部房间';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand`
--

DROP TABLE IF EXISTS `club_room_hand`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_1`
--

DROP TABLE IF EXISTS `club_room_hand_1`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_1` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_10`
--

DROP TABLE IF EXISTS `club_room_hand_10`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_10` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_11`
--

DROP TABLE IF EXISTS `club_room_hand_11`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_11` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_12`
--

DROP TABLE IF EXISTS `club_room_hand_12`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_12` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_13`
--

DROP TABLE IF EXISTS `club_room_hand_13`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_13` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_14`
--

DROP TABLE IF EXISTS `club_room_hand_14`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_14` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_15`
--

DROP TABLE IF EXISTS `club_room_hand_15`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_15` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_16`
--

DROP TABLE IF EXISTS `club_room_hand_16`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_16` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_17`
--

DROP TABLE IF EXISTS `club_room_hand_17`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_17` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_18`
--

DROP TABLE IF EXISTS `club_room_hand_18`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_18` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_19`
--

DROP TABLE IF EXISTS `club_room_hand_19`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_19` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_2`
--

DROP TABLE IF EXISTS `club_room_hand_2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_2` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_20`
--

DROP TABLE IF EXISTS `club_room_hand_20`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_20` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_21`
--

DROP TABLE IF EXISTS `club_room_hand_21`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_21` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_22`
--

DROP TABLE IF EXISTS `club_room_hand_22`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_22` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_23`
--

DROP TABLE IF EXISTS `club_room_hand_23`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_23` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_24`
--

DROP TABLE IF EXISTS `club_room_hand_24`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_24` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_25`
--

DROP TABLE IF EXISTS `club_room_hand_25`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_25` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_26`
--

DROP TABLE IF EXISTS `club_room_hand_26`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_26` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_27`
--

DROP TABLE IF EXISTS `club_room_hand_27`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_27` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_28`
--

DROP TABLE IF EXISTS `club_room_hand_28`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_28` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_29`
--

DROP TABLE IF EXISTS `club_room_hand_29`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_29` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_3`
--

DROP TABLE IF EXISTS `club_room_hand_3`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_3` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_30`
--

DROP TABLE IF EXISTS `club_room_hand_30`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_30` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_4`
--

DROP TABLE IF EXISTS `club_room_hand_4`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_4` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_5`
--

DROP TABLE IF EXISTS `club_room_hand_5`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_5` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_6`
--

DROP TABLE IF EXISTS `club_room_hand_6`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_6` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_7`
--

DROP TABLE IF EXISTS `club_room_hand_7`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_7` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_8`
--

DROP TABLE IF EXISTS `club_room_hand_8`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_8` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_hand_9`
--

DROP TABLE IF EXISTS `club_room_hand_9`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_hand_9` (
  `handId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '房间id',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `underCards` json NOT NULL COMMENT '底牌',
  `handJson` json NOT NULL COMMENT '玩家牌及输赢信息',
  `createTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`handId`),
  KEY `roomId` (`roomId`),
  KEY `clubId` (`clubId`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手牌历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_lucky`
--

DROP TABLE IF EXISTS `club_room_lucky`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_lucky` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '所属房间id',
  `uid` int(10) unsigned NOT NULL COMMENT '会员uid',
  `cardType` varchar(50) NOT NULL COMMENT '幸运牌类型',
  `cardList` json NOT NULL COMMENT '牌组',
  PRIMARY KEY (`id`),
  KEY `clubId` (`clubId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='俱乐部幸运玩家';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_room_user`
--

DROP TABLE IF EXISTS `club_room_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_room_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `clubId` int(10) unsigned NOT NULL COMMENT '俱乐部id',
  `roomId` int(10) unsigned NOT NULL COMMENT '所属房间id',
  `uid` int(10) unsigned NOT NULL COMMENT '会员uid',
  `gameType` tinyint(3) unsigned NOT NULL COMMENT '游戏类型 1=德州 2=牛加水',
  `curBet` int(10) unsigned NOT NULL COMMENT '当前筹码',
  `scale` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '服务费',
  `handNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '手牌数',
  `handNumWin` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '胜手数',
  `handNumPool` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '入池手数',
  `preAddBet` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '预约买入筹码',
  `profitLoss` int(11) NOT NULL DEFAULT '0' COMMENT '盈亏',
  `totalBuyBet` int(10) unsigned NOT NULL COMMENT '总买入筹码',
  PRIMARY KEY (`id`),
  KEY `roomId` (`roomId`,`uid`),
  KEY `clubId` (`clubId`),
  KEY `uid` (`uid`,`gameType`) USING BTREE,
  KEY `roomId_2` (`roomId`,`profitLoss`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='房间玩家实时战绩';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `country`
--

DROP TABLE IF EXISTS `country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `country` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `title` varchar(100) NOT NULL COMMENT '国家名称',
  `code` varchar(100) NOT NULL COMMENT '国家区号',
  `country` varchar(100) NOT NULL COMMENT '英文名称',
  `blindBet` varchar(255) NOT NULL COMMENT '盲注或底注',
  `rmbToclubRb` int(10) unsigned NOT NULL COMMENT '每钻石可兑换的俱乐部币数量',
  `clubRbLeast` int(10) unsigned NOT NULL COMMENT '俱乐部币兑换回钻石最少起兑数',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态 0=禁用 1=启用',
  `orders` int(11) NOT NULL DEFAULT '0' COMMENT '排序 倒序',
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='国家配置表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `game_conf`
--

DROP TABLE IF EXISTS `game_conf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `game_conf` (
  `conf_type` varchar(10) NOT NULL COMMENT '配置类型',
  `conf_key` varchar(255) NOT NULL COMMENT 'key',
  `conf_val` text NOT NULL COMMENT 'value',
  UNIQUE KEY `conf_key` (`conf_key`),
  KEY `conf_type` (`conf_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='游戏配置信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ip2nation`
--

DROP TABLE IF EXISTS `ip2nation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ip2nation` (
  `ip` int(11) unsigned NOT NULL DEFAULT '0',
  `country` char(2) NOT NULL DEFAULT '',
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ip2nationcountries`
--

DROP TABLE IF EXISTS `ip2nationcountries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ip2nationcountries` (
  `code` varchar(4) NOT NULL DEFAULT '',
  `iso_code_2` varchar(2) NOT NULL DEFAULT '',
  `iso_code_3` varchar(3) DEFAULT '',
  `iso_country` varchar(255) NOT NULL DEFAULT '',
  `country` varchar(255) NOT NULL DEFAULT '',
  `lat` float NOT NULL DEFAULT '0',
  `lon` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`code`),
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mail`
--

DROP TABLE IF EXISTS `mail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mail` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收件人uid 所有人=0 单人=uid',
  `nickname` varchar(255) DEFAULT NULL COMMENT '收件人昵称',
  `create_uid` int(11) NOT NULL DEFAULT '0' COMMENT '发件人uid',
  `create_nickname` varchar(255) DEFAULT NULL COMMENT '发件人昵称',
  `rmb` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件钻石',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=未读,1=已读',
  `is_rec_rmb` tinyint(4) NOT NULL DEFAULT '0' COMMENT '邮件领取附件 0=未领取 1=已领取',
  `createTime` int(10) unsigned NOT NULL COMMENT '时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`createTime`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='邮件';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mail_read`
--

DROP TABLE IF EXISTS `mail_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mail_read` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `mail_id` int(10) unsigned NOT NULL COMMENT '邮件id',
  `uid` int(10) unsigned NOT NULL COMMENT '玩家uid',
  `rmb_flag` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否领取钻石 0=未领取 1=已领取',
  `createTime` int(10) unsigned NOT NULL COMMENT '阅读时间',
  PRIMARY KEY (`id`),
  KEY `mail_id` (`mail_id`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='邮件已读标记';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notice`
--

DROP TABLE IF EXISTS `notice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notice` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1=发布 0=不发布',
  `createTime` int(10) unsigned NOT NULL COMMENT '时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公告';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `online`
--

DROP TABLE IF EXISTS `online`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `online` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `sid` char(32) NOT NULL COMMENT 'session ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '玩家uid',
  `nickname` varchar(255) NOT NULL DEFAULT ' ' COMMENT '玩家昵称',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='在线玩家临时表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pay_record`
--

DROP TABLE IF EXISTS `pay_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `order_no` varchar(255) NOT NULL COMMENT '订单号',
  `pay_id` varchar(255) DEFAULT NULL COMMENT 'payId',
  `uid` int(10) unsigned NOT NULL COMMENT '玩家uid',
  `num` int(10) unsigned NOT NULL COMMENT '钻石数量',
  `extra` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '赠送的数量',
  `price` int(10) unsigned NOT NULL COMMENT '价格',
  `price_type` tinyint(3) unsigned NOT NULL COMMENT '货币类型',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态 0=待支付 1=支付成功',
  `pay_type` tinyint(3) unsigned NOT NULL COMMENT '支付方式 1=paypal 2=apple 3=管理员充值',
  `order_time` int(10) unsigned NOT NULL COMMENT '下单时间',
  `order_date` varchar(255) NOT NULL COMMENT '文本下单时间',
  `pay_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
  `pay_date` varchar(255) DEFAULT NULL COMMENT '文本支付时间',
  `note` varchar(255) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_no` (`order_no`),
  KEY `pay_type` (`pay_type`,`pay_time`),
  KEY `status` (`status`,`order_time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='充值记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `public_room`
--

DROP TABLE IF EXISTS `public_room`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `public_room` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `game` varchar(50) NOT NULL COMMENT '房间类型 dzPoker=德州，cowWater=牛加水',
  `opTimeout` int(10) unsigned NOT NULL COMMENT '操作时间 单位秒',
  `costScale` float unsigned NOT NULL COMMENT '抽成比例',
  `playerNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '玩家数 5人房,7人房 牛加水专有',
  `blindBet` int(10) unsigned NOT NULL COMMENT '盲注/底注',
  `minBet` int(10) unsigned NOT NULL COMMENT '筹码下限',
  `maxBet` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '筹码上限',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='公共俱乐部房间';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shop`
--

DROP TABLE IF EXISTS `shop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shop` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `title` varchar(100) NOT NULL DEFAULT ' ' COMMENT '标题',
  `stype` varchar(20) NOT NULL COMMENT '类型 rmb=钻石 gold=金币 card=VIP卡',
  `avatar` varchar(255) NOT NULL DEFAULT ' ' COMMENT '商品图标的URL',
  `num` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '数量',
  `extra` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '赠送的数量',
  `price` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '价格',
  `param` text NOT NULL COMMENT '参数',
  `intro` text NOT NULL COMMENT '商品描述',
  `orders` smallint(6) NOT NULL DEFAULT '99' COMMENT '排序',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态 1=启用 0=禁用',
  `flag` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '显示 1=app 2=web 3=都显示',
  PRIMARY KEY (`id`),
  KEY `status` (`status`,`orders`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='商店';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '玩家id',
  `pid` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '平台id',
  `token` varchar(32) NOT NULL COMMENT '唯一登入码',
  `cid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '国家id',
  `username` varchar(50) NOT NULL COMMENT '帐号',
  `nickname` varchar(255) NOT NULL COMMENT '昵称',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像URL',
  `rmb` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '钻石',
  `gold` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '金币',
  `vip_card` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '玩家升级卡 1=银卡 2=金卡 3=黑卡',
  `vip_endtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '升级卡到期时间',
  `card_club_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '升级卡可创建俱乐部数量',
  `is_look_undercard` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否可查看公共牌',
  `card_emoji_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '升级卡免费表情数量',
  `card_delay_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '升级卡免费延时数量',
  `audio` varchar(255) DEFAULT NULL COMMENT '语音标识',
  `pos` point DEFAULT NULL COMMENT '经纬度',
  `verify_date` varchar(50) NOT NULL COMMENT '最后发送短信验证码日期',
  `verify_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送次数累计',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(255) NOT NULL DEFAULT '0' COMMENT '创建ip',
  `last_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最后登陆时间',
  `last_ip` varchar(255) NOT NULL DEFAULT '0' COMMENT '最后登录ip',
  `login_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登陆次数',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态 1=正常 0=锁定',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `token` (`token`),
  UNIQUE KEY `nickname` (`nickname`),
  UNIQUE KEY `username` (`username`,`cid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=100000 DEFAULT CHARSET=utf8 COMMENT='玩家表';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-08-14 17:15:49
