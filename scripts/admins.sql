-- MySQL dump 10.13  Distrib 8.0.20, for Linux (x86_64)
--
-- Host: localhost    Database: yobet_new_dev
-- ------------------------------------------------------
-- Server version	8.0.20

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '管理员名称',
  `password` char(32) NOT NULL DEFAULT '' COMMENT '密码',
  `role_id` int DEFAULT '0' COMMENT '管理员所属角色ID',
  `role_name` varchar(64) DEFAULT NULL COMMENT '管理员所属角色名称',
  `state` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `created` int unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `updated` int unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `deleted` int unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  `allow_ips` text COMMENT '允许访问IP',
  `salt` char(32) NOT NULL DEFAULT '' COMMENT '安全验证',
  `login_count` int unsigned NOT NULL DEFAULT '0' COMMENT '登录次数',
  `last_login` int unsigned NOT NULL DEFAULT '0' COMMENT '上次登录时间',
  `last_ip` varchar(32) NOT NULL DEFAULT '' COMMENT '上次登录IP',
  `mail` varchar(100) NOT NULL DEFAULT '' COMMENT '邮箱账号',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `username_unique` (`name`) USING BTREE COMMENT '管理员唯一索引'
) ENGINE=InnoDB AUTO_INCREMENT=206 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (37,'rose','d279f8bbfaa599d9d6d0daa2aca170ff',78,'超级管理员',0,1592546402,1592546402,0,NULL,'5rNuA23OhLDLdmrscFmdfkIhUejbGas5',0,0,'','',''),(200,'Niclolas888','d279f8bbfaa599d9d6d0daa2aca170ff',78,'超级管理员',0,1592546402,1592546402,0,NULL,'5rNuA23OhLDLdmrscFmdfkIhUejbGas5',0,0,'','',''),(201,'test01','d279f8bbfaa599d9d6d0daa2aca170ff',78,'超级管理员',0,1592546402,1592546402,0,NULL,'5rNuA23OhLDLdmrscFmdfkIhUejbGas5',0,0,'','',''),(202,'test02','d279f8bbfaa599d9d6d0daa2aca170ff',78,'超级管理员',0,1592546402,1592546402,0,NULL,'5rNuA23OhLDLdmrscFmdfkIhUejbGas5',0,0,'','',''),(203,'lydia','d279f8bbfaa599d9d6d0daa2aca170ff',78,'超级管理员',0,1592546402,1592546402,0,NULL,'5rNuA23OhLDLdmrscFmdfkIhUejbGas5',0,0,'','',''),(204,'admin','3123b75c71306358663c0cd66dc3054b',78,'超级管理员',0,1592546402,1597388103,0,NULL,'XyYXTLZvjz3IviKoBW0157ugNi831SDE',166,1597388103,'127.0.0.1','',''),(205,'admin1','3e7ed7cc10abbd7bce8c9257bdd82233',21,'财务组长',0,1595837030,1595841638,0,'12.13.13.123,12=','YC2uMiGBRh5EtKzhNZaG5HxyZblW0loI',6,1595841638,'','12313@qq.com','点点滴滴');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-08-17  9:39:51
