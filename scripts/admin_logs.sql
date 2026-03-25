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
-- Table structure for table `admin_logs`
--

DROP TABLE IF EXISTS `admin_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int unsigned NOT NULL DEFAULT '0' COMMENT '管理员编号',
  `admin_name` varchar(20) NOT NULL DEFAULT '' COMMENT '管理员名称',
  `level` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '日志级别',
  `type` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '日志类型',
  `url` varchar(200) NOT NULL DEFAULT '' COMMENT '调用地址',
  `created` int unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `remark` varchar(20) NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_logs`
--

LOCK TABLES `admin_logs` WRITE;
/*!40000 ALTER TABLE `admin_logs` DISABLE KEYS */;
INSERT INTO `admin_logs` VALUES (1,204,'admin',0,1,'/v1/index/login',1583571583,''),(2,204,'admin',0,1,'/v1/index/login',1583571883,''),(3,204,'admin',0,1,'/v1/index/login',1583572144,''),(4,204,'admin',0,1,'/v1/index/login',1583574332,''),(5,204,'admin',0,1,'/v1/index/login',1583574697,''),(6,204,'admin',0,1,'/v1/index/login',1583575339,''),(7,204,'admin',0,1,'/v1/index/login',1583736288,''),(8,204,'admin',0,1,'/v1/index/login',1583748884,''),(9,204,'admin',0,1,'/v1/index/login',1583753300,''),(10,204,'admin',0,1,'/v1/index/login',1583753360,''),(11,204,'admin',0,1,'/v1/index/login',1583754252,''),(12,204,'admin',0,1,'/v1/index/login',1583754319,''),(13,204,'admin',0,1,'/v1/index/login',1583755352,''),(14,204,'admin',0,1,'/v1/index/login',1583756842,''),(15,204,'admin',0,1,'/v1/index/login',1583808163,''),(16,204,'admin',0,1,'/v1/index/login',1583808198,''),(17,204,'admin',0,1,'/v1/index/login',1583828666,''),(18,204,'admin',0,1,'/v1/index/login',1583908313,''),(19,204,'admin',0,1,'/v1/index/login',1584062702,''),(20,204,'admin',0,1,'/v1/index/login',1584083978,''),(21,204,'admin',0,1,'/v1/index/login',1584086133,''),(22,204,'admin',0,1,'/v1/index/login',1584086400,''),(23,204,'admin',0,1,'/v1/index/login',1584090106,''),(24,204,'admin',0,1,'/v1/index/login',1584091298,''),(25,204,'admin',0,1,'/v1/index/login',1584091298,''),(26,204,'admin',0,1,'/v1/index/login',1584091358,''),(27,204,'admin',0,1,'/v1/index/login',1584094070,''),(28,204,'admin',0,1,'/v1/index/login',1584148330,''),(29,204,'admin',0,1,'/v1/index/login',1584148883,''),(30,204,'admin',0,1,'/v1/index/login',1584151553,''),(31,204,'admin',0,1,'/v1/index/login',1584151769,''),(32,204,'admin',0,1,'/v1/index/login',1584151824,''),(33,204,'admin',0,1,'/v1/index/login',1584151828,''),(34,204,'admin',0,1,'/v1/index/login',1584152017,''),(35,204,'admin',0,1,'/v1/index/login',1584152284,''),(36,204,'admin',0,1,'/v1/index/login',1584152403,''),(37,204,'admin',0,1,'/v1/index/login',1584153509,''),(38,204,'admin',0,1,'/v1/index/login',1584324892,''),(39,204,'admin',0,1,'/v1/index/login',1584325786,''),(40,204,'admin',0,1,'/v1/index/login',1584337681,''),(41,204,'admin',0,1,'/v1/index/login',1584339380,''),(42,204,'admin',0,1,'/v1/index/login',1584339738,''),(43,204,'admin',0,1,'/v1/index/login',1584340395,''),(44,204,'admin',0,1,'/v1/index/login',1584354454,''),(45,204,'admin',0,1,'/v1/index/login',1584589520,''),(46,204,'admin',0,1,'/v1/index/login',1584589654,''),(47,204,'admin',0,1,'/v1/index/login',1584590125,''),(48,204,'admin',0,1,'/v1/index/login',1584590301,''),(49,204,'admin',0,1,'/v1/index/login',1584590431,'');
/*!40000 ALTER TABLE `admin_logs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-08-17  9:49:39
