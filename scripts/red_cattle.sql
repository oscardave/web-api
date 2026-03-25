-- MySQL dump 10.13  Distrib 8.0.28, for Linux (x86_64)
--
-- Host: localhost    Database: red_cattle
-- ------------------------------------------------------
-- Server version	8.0.28-0ubuntu0.20.04.3

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
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(60) NOT NULL DEFAULT '' COMMENT '密码',
  `last_login_time` int NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `last_login_ip` varchar(20) NOT NULL DEFAULT '0' COMMENT '最后登录ip',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '用户状态：0禁用，1正常',
  `role_id` int NOT NULL COMMENT '角色ID【0超级管理员】',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '最后修改时间',
  `login_count` int NOT NULL DEFAULT '0' COMMENT '登录次数',
  `allow_ip` varchar(256) NOT NULL DEFAULT '' COMMENT '授权ip',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT COMMENT='管理员账户';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES (1,'admin','$2y$10$utj7n5H6CmXHIO0YQuswCeKWJ5cfB9WGx5/4COjLw.AlN6UOZKmrC',1645701898,'127.0.0.1',1,0,1622276950,1645701898,27,'127.0.0.1'),(2,'herher','$2y$10$L8MJfYugO8fPHmcFH6aurOfT3ipgFzXD9nuW70lE.fXWOtobuZgKG',1634692727,'207.46.138.114',1,1,1632922233,0,0,'127.0.0.1'),(3,'sheshe','$2y$10$SHNRFbY2UoPUrgxe4QlVduJQ4jAn1FGaMoWgmwo7kueL4NxexHq6.',1637039963,'119.8.61.48',1,4,1632922222,0,0,'127.0.0.1'),(5,'xiongdi','$2y$10$dTNTEPATgP.1cA.XkdmINOytUWnk04vzUVp4DUEjy84JXuHCp2pxa',1637133616,'13.75.2.175',1,3,1632922211,0,0,'127.0.0.1'),(6,'sheshe2','$2y$10$Z1CHfeWFqliWaNS31rb/uexn8QgLSJdk0PmSfzI99pSc4S1nBwJiS',0,'0',1,4,1632922198,0,0,'127.0.0.1'),(7,'herher3','$2y$10$148dxnIK7ey/FRDgfTxm1O0F.ybvqTE.K51Ods0hwHOeEx3m0ybG.',1634692752,'207.46.138.114',2,4,1632922185,1642734774,0,'127.0.0.1');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_log`
--

DROP TABLE IF EXISTS `admin_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int unsigned NOT NULL DEFAULT '0' COMMENT '管理员编号',
  `admin_name` varchar(20) NOT NULL DEFAULT '' COMMENT '管理员名称',
  `object_id` int unsigned NOT NULL DEFAULT '0' COMMENT '修改对象',
  `object_name` varchar(20) NOT NULL DEFAULT '' COMMENT '修改对象名称',
  `level` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '日志级别 0:调试 1:普通 2:警告 3:危险 4:致命 5:错误',
  `type` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '日志类型 0:登录退出 1:财务调整 2:会员管理 3:内容管理 4:系统设置 5:其他',
  `value_old` varchar(256) NOT NULL DEFAULT '' COMMENT '旧值',
  `value_new` varchar(256) NOT NULL DEFAULT '' COMMENT '新值',
  `module` varchar(64) NOT NULL DEFAULT '' COMMENT '模块',
  `url` varchar(200) NOT NULL DEFAULT '' COMMENT '调用地址',
  `created` int unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `remark` varchar(256) NOT NULL DEFAULT '' COMMENT '备注',
  `ip` varchar(64) NOT NULL DEFAULT '' COMMENT 'ip',
  PRIMARY KEY (`id`),
  KEY `admin_log_admin_id_idx` (`admin_id`),
  KEY `admin_log_object_id_idx` (`object_id`),
  KEY `admin_log_level_idx` (`level`),
  KEY `admin_log_type_idx` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=100075 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_log`
--

LOCK TABLES `admin_log` WRITE;
/*!40000 ALTER TABLE `admin_log` DISABLE KEYS */;
INSERT INTO `admin_log` VALUES (100000,1,'admin',1,'admin',1,0,'','','index','Array',1641825479,'后台用户登录',''),(100001,1,'admin',1,'admin',1,0,'','','index','/index/login',1641825508,'后台用户登录',''),(100002,1,'admin',1,'admin',1,0,'','','index','/index/login',1641970484,'后台用户登录',''),(100003,1,'admin',1,'admin',1,0,'','','index','/index/login',1641989397,'后台用户登录','127.0.0.1'),(100004,1,'admin',1,'admin',1,0,'','','index','/index/login',1642576368,'后台用户登录','127.0.0.1'),(100005,1,'admin',1,'admin',1,0,'','','index','/index/login',1642576620,'后台用户登录','127.0.0.1'),(100006,1,'admin',1,'admin',1,0,'','','index','/index/login',1642603556,'后台用户登录','127.0.0.1'),(100007,1,'admin',1,'admin',1,0,'','','index','/index/login',1642604637,'后台用户登录','127.0.0.1'),(100008,1,'admin',1,'admin',1,0,'','','index','/index/login',1642604684,'后台用户登录','127.0.0.1'),(100009,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1642604692,'用户修改 系统设置 - 签到赠送产品','127.0.0.1'),(100010,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1642606750,'用户修改 系统设置 - 网站设置','127.0.0.1'),(100011,1,'admin',1,'admin',1,0,'','','index','/index/login',1642661582,'后台用户登录','127.0.0.1'),(100012,1,'admin',1,'admin',1,0,'','','index','/index/login',1642662641,'后台用户登录','127.0.0.1'),(100013,1,'admin',1,'admin',1,0,'','','index','/index/login',1642664335,'后台用户登录','127.0.0.1'),(100014,1,'admin',1,'admin',1,0,'','','index','/index/login',1642664529,'后台用户登录','127.0.0.1'),(100015,1,'admin',1,'admin',1,0,'','','index','/index/login',1642664562,'后台用户登录','127.0.0.1'),(100016,1,'admin',1,'admin',1,0,'','','/products/change_status','http://ock.local/products/change_status',1642669683,'修改商品状态为: ','127.0.0.1'),(100017,1,'admin',1,'admin',1,0,'','','/products/change_status','http://ock.local/products/change_status',1642669699,'修改商品状态为: 3','127.0.0.1'),(100018,1,'admin',1,'admin',1,0,'','','/products/change_status','http://ock.local/products/change_status',1642669751,'修改商品状态为: ','127.0.0.1'),(100019,1,'admin',1,'admin',1,0,'','','/products/change_status','http://ock.local/products/change_status',1642669755,'修改商品状态为: 2','127.0.0.1'),(100020,1,'admin',1,'admin',1,0,'','','/products/change_status','http://ock.local/products/change_status',1642669828,'修改商品状态为: ','127.0.0.1'),(100021,1,'admin',1,'admin',1,0,'','','/products/change_status','http://ock.local/products/change_status',1642670496,'修改商品状态为: 2','127.0.0.1'),(100022,1,'admin',1,'admin',1,0,'','','/products/change_status','http://ock.local/products/change_status',1642670501,'修改商品状态为: ','127.0.0.1'),(100023,1,'admin',1,'admin',1,0,'','','index','/index/login',1642731873,'后台用户登录','127.0.0.1'),(100024,1,'admin',1,'admin',1,0,'','','index','/index/login',1642750672,'后台用户登录','127.0.0.1'),(100025,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1642751506,'用户修改 系统设置 - 网站设置','127.0.0.1'),(100026,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1642751511,'用户修改 系统设置 - 网站图片设置','127.0.0.1'),(100027,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1642751515,'用户修改 系统设置 - 收款账号','127.0.0.1'),(100028,1,'admin',1,'admin',1,0,'','','index','/index/login',1642777080,'后台用户登录','127.0.0.1'),(100029,1,'admin',1,'admin',1,0,'','','index','/index/login',1642829350,'后台用户登录','127.0.0.1'),(100030,1,'admin',1,'admin',1,0,'','','index','/index/login',1642849098,'后台用户登录','127.0.0.1'),(100031,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1642857637,'用户修改 系统设置 - 网站图片设置','127.0.0.1'),(100032,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1642858370,'用户修改 系统设置 - 网站图片设置','127.0.0.1'),(100033,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1642858478,'用户修改 系统设置 - 网站图片设置','127.0.0.1'),(100034,1,'admin',1,'admin',1,0,'','','index','/index/login',1643008206,'后台用户登录','127.0.0.1'),(100035,1,'admin',1,'admin',1,0,'','','index','/index/login',1643008342,'后台用户登录','127.0.0.1'),(100036,1,'admin',1,'admin',1,0,'','','index','/index/login',1643008490,'后台用户登录','127.0.0.1'),(100037,1,'admin',1,'admin',1,0,'','','index','/index/login',1643028134,'后台用户登录','127.0.0.1'),(100038,1,'admin',1,'admin',1,0,'','','index','/index/login',1643040333,'后台用户登录','127.0.0.1'),(100039,1,'admin',1,'admin',1,0,'','','index','/index/login',1643040602,'后台用户登录','127.0.0.1'),(100040,1,'admin',1,'admin',1,0,'','','index','/index/login',1643041072,'后台用户登录','127.0.0.1'),(100041,1,'admin',1,'admin',1,0,'','','index','/index/login',1643041165,'后台用户登录','127.0.0.1'),(100042,1,'admin',1,'admin',1,0,'','','index','/index/login',1643093642,'后台用户登录','127.0.0.1'),(100043,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643096127,'用户修改 系统设置 - 网站图片设置','127.0.0.1'),(100044,1,'admin',1,'admin',1,0,'','','index','/index/login',1643115203,'后台用户登录','127.0.0.1'),(100045,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643116500,'用户修改 系统设置 - 网站图片设置','127.0.0.1'),(100046,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643116512,'用户修改 系统设置 - 网站图片设置','127.0.0.1'),(100047,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643116527,'用户修改 系统设置 - 网站图片设置','127.0.0.1'),(100048,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643116537,'用户修改 系统设置 - 网站图片设置','127.0.0.1'),(100049,1,'admin',1,'admin',1,0,'','','index','/index/login',1643435467,'后台用户登录','127.0.0.1'),(100050,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643435850,'用户修改 系统设置 - 网站图片设置','127.0.0.1'),(100051,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643443248,'用户修改 系统设置 - 注册赠送产品','127.0.0.1'),(100052,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643443261,'用户修改 系统设置 - 注册赠送产品','127.0.0.1'),(100053,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643443275,'用户修改 系统设置 - 注册赠送产品','127.0.0.1'),(100054,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643443456,'用户修改 系统设置 - 注册赠送产品','127.0.0.1'),(100055,1,'admin',1,'admin',1,0,'','','index','/index/login',1643451125,'后台用户登录','127.0.0.1'),(100056,1,'admin',1,'admin',1,0,'','','index','/index/login',1643511688,'后台用户登录','127.0.0.1'),(100057,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643519372,'用户修改 系统设置 - 网站设置','127.0.0.1'),(100058,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643520135,'用户修改 系统设置 - 网站设置','127.0.0.1'),(100059,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643520165,'用户修改 系统设置 - 网站设置','127.0.0.1'),(100060,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643520225,'用户修改 系统设置 - 网站图片设置','127.0.0.1'),(100061,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643520229,'用户修改 系统设置 - 收款账号','127.0.0.1'),(100062,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643520232,'用户修改 系统设置 - 返佣比例','127.0.0.1'),(100063,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643520234,'用户修改 系统设置 - 验证码配置','127.0.0.1'),(100064,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643520237,'用户修改 系统设置 - 支付宝配置','127.0.0.1'),(100065,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643520244,'用户修改 系统设置 - 验证码配置','127.0.0.1'),(100066,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643520248,'用户修改 系统设置 - 支付宝配置','127.0.0.1'),(100067,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643520262,'用户修改 系统设置 - 支付宝配置','127.0.0.1'),(100068,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643520266,'用户修改 系统设置 - 注册配置','127.0.0.1'),(100069,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643520269,'用户修改 系统设置 - 注册赠送产品','127.0.0.1'),(100070,1,'admin',1,'admin',1,4,'','','/configs/save_config','http://ock.local/configs/save_config',1643520272,'用户修改 系统设置 - 签到赠送产品','127.0.0.1'),(100071,1,'admin',1,'admin',1,0,'','','index','/index/login',1644110361,'后台用户登录','127.0.0.1'),(100072,1,'admin',1,'admin',1,0,'','','index','/index/login',1644242338,'后台用户登录','127.0.0.1'),(100073,1,'admin',1,'admin',1,0,'','','index','/index/login',1644243678,'后台用户登录','127.0.0.1'),(100074,1,'admin',1,'admin',1,0,'','','index','/index/login',1645701898,'后台用户登录','127.0.0.1');
/*!40000 ALTER TABLE `admin_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `article`
--

DROP TABLE IF EXISTS `article`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `article` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '资讯标题',
  `desc` text COMMENT '资讯描述',
  `content` text,
  `images` varchar(255) NOT NULL DEFAULT '' COMMENT '资讯封面',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `article`
--

LOCK TABLES `article` WRITE;
/*!40000 ALTER TABLE `article` DISABLE KEYS */;
INSERT INTO `article` VALUES (19,'华夏云投—开春高速公路项目','开春高速公路项目介绍','<p style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin-top: 0px; margin-bottom: 16px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; white-space: normal; background-color: rgb(255, 255, 255);\">央广网江门1月1日消息（记者周羽 通讯员肖明葵、杨昆、高松、张帮喜、吕传龙）2020年12月31日，广东省“十三五”高速公路路网规划重点工程、粤西对接“大湾区”核心区域大型基础性工程——开春高速公路正式建成通车。</p><p style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin-top: 0px; margin-bottom: 16px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; white-space: normal; background-color: rgb(255, 255, 255);\"><img src=\"/public/uploads/20210730/1627631807484770.png\" title=\"1627631807484770.png\" alt=\"139640427_16097444382531n.png\"/></p><p style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin-top: 0px; margin-bottom: 16px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; white-space: normal; background-color: rgb(255, 255, 255);\">开春高速公路建成通车后，粤港澳大湾区从此与粤西地区、北部湾经济区串联在一起，对促进沿线经济社会发展，实现广东区域发展协调均衡具有十分重要的意义。这条高速未来将成为串联起粤西地区连通粤港澳大湾区与广西北部湾经济区的交通大动脉，进一步打通“大湾区”与东盟经济带。</p><p style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin-top: 0px; margin-bottom: 16px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; white-space: normal; background-color: rgb(255, 255, 255);\">开春高速与中开高速交于罗汉山枢纽，经恩平市君堂镇、牛江镇、良西镇、大田镇，阳春市春湾镇、合水镇，终于阳春市陂面镇南星村，与汕湛高速接于南星互通枢纽，采用双向6车道，设计时速120公里标准建设。主线有特大桥、大桥（含互通立交主线桥）56座、中小桥15座；长隧道7座、中短隧道3座。</p><p style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin-top: 0px; margin-bottom: 16px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; white-space: normal; background-color: rgb(255, 255, 255);\">从粤西到大湾区? 三小时车程缩短至40多分钟</p><p style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin-top: 0px; margin-bottom: 16px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; white-space: normal; background-color: rgb(255, 255, 255);\">“以前春湾镇到开平，需要三个多小时车程，现在有了开春高速，不到一个小时就够了，我们是真正融入了粤港澳大湾区，进入一小时经济生活圈。”开春高速的通车，让阳春市春湾镇党委副书记梁仲辉喜不自禁。</p><p style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin-top: 0px; margin-bottom: 16px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; white-space: normal; background-color: rgb(255, 255, 255);\">粤西山区，“山连着山、谷挨着谷”，当地百姓受困于大山阻隔，很难走出去。中交四航局开春高速TJ05标项目书记李亮东告诉记者：“新建成的开春高速公路连接起了开平、恩平和阳春三地，不仅将原本三个多小时的车程缩短为40多分钟，未来随着西沿线规划的启动，开春高速将成为深圳到南宁最佳的出行选择，是连接大湾区及北部湾的大通道。”</p><p style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin-top: 0px; margin-bottom: 16px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; white-space: normal; background-color: rgb(255, 255, 255);\">为了尽快建成这条高速，建设者克服了重重困难与挑战。开春高速贯穿粤西山岭重丘区，岩溶地质复杂，桥隧比高达55%，其中四标和六标桥隧比甚至超过75%，桥梁隧道施工难度非常大。“2016年底，我来进行项目前期筹备工作的时候，连路都没有，到处崇山峻岭，我们一个工点就要走一整天。”中交四航局开春高速TJ06标项目经理黎韶梁回忆起初来春湾镇的场景，仍记忆犹新。</p><p style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin-top: 0px; margin-bottom: 16px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; white-space: normal; background-color: rgb(255, 255, 255);\">“面对难题，我们主动钻研，开发出了桥梁高墩液压顶升模架系统、双悬臂桁架梁托架现浇空心薄壁墩墩帽等施工技术，部分工艺经中国公路建设行业协会鉴定，已经达到了‘国内领先’水平。我们还经受住了超强台风‘山竹’的袭击，连续暴雨导致的山体滑坡和今年新冠肺炎疫情的挑战，这一路走来真是坎坷不断！”中交开春高速公路项目有限公司（以下简称“开春项目公司”）总经理李宝锋亲历了项目建设的全过程。</p><p style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin-top: 0px; margin-bottom: 16px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; white-space: normal; background-color: rgb(255, 255, 255);\">同样，地方政府的支持也让项目建设事半功倍。“进场后不到半年，地方政府就完成了80%至90%的征地拆迁任务，建设中遇到问题，政府第一时间支持，随时解决难题。”分管征拆工作的开春项目公司副总经理卢庆涛非常感激政府的支持与配合。</p><p style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin-top: 0px; margin-bottom: 16px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; white-space: normal; background-color: rgb(255, 255, 255);\">“开春高速修到家门口，这是我们的致富路”</p><p style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin-top: 0px; margin-bottom: 16px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; white-space: normal; background-color: rgb(255, 255, 255);\">“有了开春高速公路，我们村700多亩、300多万斤的橙子运到广州、深圳、珠海等地比原来的车程至少缩短了一个小时，而且，外面的老板也更愿意来我们的果园来摘果了。”恩平市大田镇朗北村村民梁金水站在自家的果园，指着不远处的高速路口，向记者兴奋地介绍着。“高速路修到了家门口，只要我们勤劳肯干，就一定会富起来。”</p><p style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin-top: 0px; margin-bottom: 16px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; white-space: normal; background-color: rgb(255, 255, 255);\">开平、恩平、阳春三地资源极为丰富，春砂仁、水稻、香芋、恩州奇石、水泥等产量丰富。这里的开平碉楼位列世界文化遗产名录，有中国温泉之乡——恩平，还有位于大田镇的广东人民抗日解放军司令部旧址。“开春高速修通给我们当地旅游业发展和农副产品的输出提供了最好的平台，来自全国各地的游客就可以更便捷地来大田旅游了。”恩平市大田镇党委书记吴活抗对未来充满了期待。恩平市农业局相关负责人则表示将用好这条“致富路”，优化调整恩平市农业发展区域布局，力争打造粤港澳大湾区高质量农业合作发展平台和优质农副产品集散中心。</p><p style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin-top: 0px; margin-bottom: 16px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; white-space: normal; background-color: rgb(255, 255, 255);\">要致富先修路，路通了，发展才有了机会。据统计，开春高速全线共扩建、改建、新建施工便道近200公里，耗资近1.2亿元，在满足项目建设需求的同时，也极大地方便了沿线村民出行和生活。</p><p style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin-top: 0px; margin-bottom: 16px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; white-space: normal; background-color: rgb(255, 255, 255);\">开春高速，一条安全、舒适、环保、快捷的康庄大路</p><p style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin-top: 0px; margin-bottom: 16px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; white-space: normal; background-color: rgb(255, 255, 255);\"><img src=\"/public/uploads/20210730/1627631842367893.png\" title=\"1627631842367893.png\" alt=\"139640427_16097444708651n.png\"/></p><p style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin-top: 0px; margin-bottom: 16px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; white-space: normal; background-color: rgb(255, 255, 255);\">行驶在开春高速上，平稳舒适是最直观的感受。“开春高速全线最大坡比仅为2.2，优于国家标准，特别是开平往西，有十几公里的爬坡，我们不断地对隧道、大桥进行优化，降低坡度，保证司乘的舒适性。”开春项目公司副总经理、总工程师李汉武在接受记者采访时表示。</p><p style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin-top: 0px; margin-bottom: 16px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; white-space: normal; background-color: rgb(255, 255, 255);\">在红旗特大桥、新东大桥等桥梁两侧，建设者为桥梁设置了像渔网一样的东西。“那是防抛网，能有效防止高速上的垃圾进入河流，污染水道。”开春高速途经饮用水源保护区、国家级水产种质资源保护区、生态严控区等，负责该段施工的中交四航局开春高速公路TJ04标项目负责人鹿昌辉介绍，设置防抛网虽然增加了成本，但对沿线的水资源保护起到了很好的作用。</p><p style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin-top: 0px; margin-bottom: 16px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; white-space: normal; background-color: rgb(255, 255, 255);\">“我们还在主要桥梁和弯道设置了震荡线，增强提醒警示的功能。”开春高速项目公司副总工程师杨华炳说，这些震荡线的设置提醒司机是否偏离了车道，体现了高速公路建设者对司机安全的人文关怀。</p><p style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin-top: 0px; margin-bottom: 16px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; white-space: normal; background-color: rgb(255, 255, 255);\">据记者了解，开春高速还有一大便民服务亮点措施，就是根据国家在节假日单向车流量较大的特点，将线路两侧的服务区打通，避免一侧服务区爆满，而一侧服务区无人使用的情况，更方便了人们的出行。</p><p><br/></p>','/public/uploads/20210730/023f6560e9c900ff801efe6f5e75b4cb.jpg',1627450298,0),(20,'华夏云投—昌九高铁项目','昌九高铁项目介绍','<p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; overflow-y: auto; max-width: 100%; line-height: 24px;\"><span class=\"bjh-p\" style=\"max-width: 100%;\">昌九高铁，是京港高铁正线庐山站安九场至昌吉赣高铁线路所，已经获得了国铁集团与江西省政府的联合批复，正在进行开工前的准备工作。按照批复消息，昌九高铁设计时速为350km/h，正线全长137公里，新建133.6公里，总投资约为315.9亿元。昌九高铁全线设站5座，分别是在建庐山站、新建庐山南站、新建共青城东站，新建昌北机场站，在建南昌东站。</span></p><p><img src=\"/public/uploads/20210730/1627630465215802.jpeg\" title=\"1627630465215802.jpeg\" alt=\"5243fbf2b2119313dda72485bd673edf90238d83.jpeg\"/><span class=\"bjh-image-caption\" style=\"display: block; margin-top: 11px; text-align: center; font-size: 13px; color: rgb(153, 153, 153);\">昌九高铁线路图</span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; overflow-y: auto; max-width: 100%; line-height: 24px;\"><span class=\"bjh-p\" style=\"max-width: 100%;\">既然昌九高铁可研报告获批了，那么这个项目什么时候能够开工建设呢？什么时候能够建好，能够实现京港高铁南段全线贯通呢？<span class=\"bjh-a\" style=\"max-width: 100%;\">长三角再建跨海高铁，投资超1070亿，跨过上海连接江浙两省</span></span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; overflow-y: auto; max-width: 100%; line-height: 24px;\"><span class=\"bjh-p\" style=\"max-width: 100%;\">这不，九江市就来了消息。从《九江市国民经济和社会发展第十四个五年规划和二Ο三五年远景目标纲要》新闻发布会上得知，昌九高铁全线将在年内开工建设，并且时间就安排在今年的下半年。最主要的是昌九高铁下半年不仅仅是先导段开工，而是全线动工。从九江市的这个新闻发布会获得最新的关于昌九高铁的消息就是：昌九客专项目相关工作进展顺利，计划下半年全线开工。</span></p><p><img src=\"/public/uploads/20210730/1627630487628247.jpeg\" title=\"1627630487628247.jpeg\" alt=\"c8ea15ce36d3d53990e80924f5d8db58372ab0e3.jpeg\"/><span class=\"bjh-image-caption\" style=\"display: block; margin-top: 11px; text-align: center; font-size: 13px; color: rgb(153, 153, 153);\">昌九高铁可研批复消息</span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; overflow-y: auto; max-width: 100%; line-height: 24px;\"><span class=\"bjh-p\" style=\"max-width: 100%;\">昌九高铁称为江西省造价最高的高铁，是毫无疑问的，全线才新建133.6公里，造价却高达315.9亿元，平均造价达到了2.3645亿/公里，这还是不包含庐山站的改扩建工程款17.5亿元的。而南昌东站还是单独建设项目，可见，昌九高铁在江西省所有高铁项目中，造价确实高。这么高的造价，希望能够又快又稳的建成，早日实现京港高铁南段贯通，结合京雄商高铁，组建京港高铁，形成国家八纵八横的重要路网。<span class=\"bjh-a\" style=\"max-width: 100%;\">九江庐山站，九江市掏腰包17.5亿打造6万㎡站房，已经下达2亿了。</span></span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; overflow-y: auto; max-width: 100%; line-height: 24px;\"><span class=\"bjh-p\" style=\"max-width: 100%;\"><span class=\"bjh-a\" style=\"max-width: 100%;\"><img src=\"/public/uploads/20210730/1627630542538254.jpeg\" title=\"1627630542538254.jpeg\" alt=\"7acb0a46f21fbe09b851877ca43f3e3b8744ad37.jpeg\"/></span></span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; overflow-y: auto; max-width: 100%; line-height: 24px;\"><span class=\"bjh-p\" style=\"max-width: 100%;\"><span class=\"bjh-a\" style=\"max-width: 100%;\"><br/></span></span></p><p><span class=\"bjh-image-caption\" style=\"display: block; margin-top: 11px; text-align: center; font-size: 13px; color: rgb(153, 153, 153);\"><br/></span></p>','/public/uploads/20210730/d0689fa91790bfb449f59b2961d89046.jpg',1627450690,0),(21,'华夏云投—嘉兴1号海上风电项目','嘉兴1号海上风电项目介绍','<p><span style=\"color: rgb(102, 102, 102); font-family: 宋体; font-size: 20px;\">面朝大海，春暖花开。眼下，正是大雪纷飞的季节。可在平湖市独山港经济开发区沿海附近，却是一片生机，激情澎湃。<br/><img src=\"/public/uploads/20210730/1627630315412910.png\" title=\"1627630315412910.png\" alt=\"36312e706bc92838af206bb7b10d5587.png\"/></span></p><p><span style=\"font-size: 20px;\"><span style=\"font-size: 18px; color: rgb(102, 102, 102); font-family: 宋体;\">12月8日上午，浙江省内在建规模最大的海上风电场工程——总投资约55.82亿元的浙能嘉兴1号海上风电场项目正式启动建设，这也意味着，未来嘉兴的电网中将加入“平湖产”海上风电这一清洁能源的影子。</span><br/><br/><span style=\"font-size: 18px; color: rgb(102, 102, 102); font-family: 宋体;\">据了解，浙能嘉兴1 号海上风电场位于杭州湾平湖海域，风场中心点离岸约20km，南北长约14.0km，东西宽约4.3km，涉海总面积约48km2。这里将安装总装机容量300兆瓦的75台4.0兆瓦的海上风电机组，同步建设一座220千伏海上升压站和一座陆上计量站，由220千伏海底电缆接入陆上计量站，再送至嘉兴电网。工程建设总工期为36个月，预计到2019年12月底首批机组发电。</span><br/><br/><span style=\"font-size: 18px; color: rgb(102, 102, 102); font-family: 宋体;\">项目建成后，年税收额将达1亿元，并且每年可贡献可再生能源电量74495万千瓦时，每年可节约标煤23万吨，减少排放温室气体二氧化碳51万吨，减少排放二氧化硫4474吨，环境效益十分显著。</span><br/><br/><span style=\"font-size: 18px; color: rgb(102, 102, 102); font-family: 宋体;\">开工仪式上，平湖市相关负责人说，这个项目的建设对缓解当地能源和环境压力，优化产业结构，推进产业升级，加快区域经济发展，保持社会可持续发展等都具有重要意义。</span><br/><br/><span style=\"font-size: 18px; color: rgb(102, 102, 102); font-family: 宋体;\">值得一提的是，浙能嘉兴1号海上风电项目开工后，总投资52亿元的华能嘉兴2号海上风电项目也将启动建设。届时，平湖市海上风电总容量将达60万千瓦，将成为全省海上风电规模最大的县级市。</span><br/></span><br/></p>','/public/uploads/20210730/ab54b326052e39ff729d1b3e7c1d66c5.jpg',1627451369,0),(22,'华夏云投—舟岱跨海大桥项目','舟岱跨海大桥项目介绍','<h1 class=\"post_title\" style=\"box-sizing: inherit; margin: 0px; padding: 0px; font-variant-numeric: normal; font-variant-east-asian: normal; font-stretch: normal; font-size: 38px; line-height: 48px; font-family: &quot;MicrosoftYaHei Bold&quot;, MicrosoftYaHei, Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; color: rgb(64, 64, 64); white-space: normal;\"><span style=\"font-size: 24px;\"><br/></span></h1><h1 class=\"post_title\" style=\"box-sizing: inherit; margin: 0px; padding: 0px; font-variant-numeric: normal; font-variant-east-asian: normal; font-stretch: normal; font-size: 38px; line-height: 48px; font-family: &quot;MicrosoftYaHei Bold&quot;, MicrosoftYaHei, Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; color: rgb(64, 64, 64); white-space: normal;\"><span style=\"font-size: 24px;\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;浙江舟山：舟岱大桥海上互通施工已近尾声，总长4.169公里</span><br/></h1><p><span style=\"font-size: 24px;\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;<span style=\"font-size: 12px;\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<span style=\"font-size: 18px;\"><span style=\"color: rgb(153, 153, 153); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 14px;\">2021-02-01 11:11:25</span></span></span><br/></span></p><p><span style=\"font-size: 24px;\"><span style=\"font-size: 12px;\"><span style=\"font-size: 18px;\"><span style=\"color: rgb(153, 153, 153); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 14px;\"><span style=\"color: rgb(64, 64, 64); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 18px; text-align: justify;\">2021年1月31日，浙江舟山，一段重达130余吨的钢箱梁正在浙江交通集团舟岱大桥海上互通段B匝道上就位。</span></span></span></span></span></p><p><span style=\"font-size: 24px;\"><span style=\"font-size: 12px;\"><span style=\"font-size: 18px;\"><span style=\"color: rgb(153, 153, 153); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 14px;\"><span style=\"color: rgb(64, 64, 64); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 18px; text-align: justify;\"><img src=\"/public/uploads/20210730/1627630140131449.jpg\" title=\"1627630140131449.jpg\" alt=\"下载.jpg\"/></span></span></span></span></span></p><p><span style=\"font-size: 24px;\"><span style=\"font-size: 12px;\"><span style=\"font-size: 18px;\"><span style=\"color: rgb(153, 153, 153); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 14px;\"><span style=\"color: rgb(64, 64, 64); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 18px; text-align: justify;\"><span style=\"color: rgb(64, 64, 64); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 18px; text-align: justify;\">据了解，由中国建筑股份有限公司承建舟岱大桥2标段长白海上互通由五条匝道组成，从上往下看犹如一片超大苜蓿叶。匝道变宽段上部均采用钢箱梁结构，总长4.169公里，共65片钢箱梁，目前已完成61片钢箱梁的安装，吊装施工已接近尾声。</span></span></span></span></span></span></p><p><span style=\"font-size: 24px;\"><span style=\"font-size: 12px;\"><span style=\"font-size: 18px;\"><span style=\"color: rgb(153, 153, 153); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 14px;\"><span style=\"color: rgb(64, 64, 64); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 18px; text-align: justify;\"><span style=\"color: rgb(64, 64, 64); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 18px; text-align: justify;\"><img src=\"/public/uploads/20210730/1627630165542338.jpg\" title=\"1627630165542338.jpg\" alt=\"下载 (1).jpg\"/></span></span></span></span></span></span></p><p><span style=\"font-size: 24px;\"><span style=\"font-size: 12px;\"><span style=\"font-size: 18px;\"><span style=\"color: rgb(153, 153, 153); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 14px;\"><span style=\"color: rgb(64, 64, 64); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 18px; text-align: justify;\"><span style=\"color: rgb(64, 64, 64); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 18px; text-align: justify;\"><span style=\"color: rgb(64, 64, 64); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 18px; text-align: justify;\">舟岱跨海大桥南接舟山本岛，北接岱山岛，远期与北向大通道连接，成为连接宁波、舟山、上海的海上大通道。</span></span></span></span></span></span></span></p><p><span style=\"font-size: 24px;\"><span style=\"font-size: 12px;\"><span style=\"font-size: 18px;\"><span style=\"color: rgb(153, 153, 153); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 14px;\"><span style=\"color: rgb(64, 64, 64); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 18px; text-align: justify;\"><span style=\"color: rgb(64, 64, 64); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 18px; text-align: justify;\"><span style=\"color: rgb(64, 64, 64); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 18px; text-align: justify;\"><img src=\"/public/uploads/20210730/1627630189834289.jpg\" title=\"1627630189834289.jpg\" alt=\"下载 (2).jpg\"/></span></span></span></span></span></span></span></p><p><span style=\"font-size: 24px;\"><span style=\"font-size: 12px;\"><span style=\"font-size: 18px;\"><span style=\"color: rgb(153, 153, 153); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 14px;\"><span style=\"color: rgb(64, 64, 64); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 18px; text-align: justify;\"><span style=\"color: rgb(64, 64, 64); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 18px; text-align: justify;\"><span style=\"color: rgb(64, 64, 64); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 18px; text-align: justify;\"><span style=\"color: rgb(64, 64, 64); font-family: Arial, &quot;Hiragino Sans GB&quot;, STHeiti, &quot;Helvetica Neue&quot;, Helvetica, &quot;Microsoft Yahei&quot;, &quot;WenQuanYi Micro Hei&quot;, sans-serif; font-size: 18px; text-align: justify;\">舟岱大桥南通航孔桥为双塔整幅钢箱梁斜拉桥，主跨390米，全长750米。全桥钢箱梁划分为A～H共8种节段类型、55个节段，其中1标段共27个节段钢箱梁，钢箱梁中心线处高3.5米，全宽34米。主梁标准节段长16米，最大起重量约为303吨。斜拉索为空间扇形双索面，共计44束，拉索采用双层防护的平行高强度镀锌钢丝，最长199.443米，最重11.36吨，此次合龙梁段NA13长12米、宽34米、高3.5米、重达230吨、吊高为53.5米。</span></span></span></span></span></span></span></span></p><p><br/></p><p><br/></p>','/public/uploads/20210730/e34b07e51f6369ec099e078d207edc13.jpg',1627452057,0),(23,'华夏云投—白鹤滩水电站项目','白鹤滩水电站项目介绍','<h1 class=\"article__title\" style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin: 0px 0px 10px; overflow-wrap: break-word; color: rgb(64, 64, 64); letter-spacing: 1px; font-size: 20px; line-height: 30px; font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; white-space: normal; background-color: rgb(255, 255, 255);\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</h1><h1 class=\"article__title\" style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin: 0px 0px 10px; overflow-wrap: break-word; color: rgb(64, 64, 64); letter-spacing: 1px; font-size: 20px; line-height: 30px; font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; white-space: normal; background-color: rgb(255, 255, 255);\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 白鹤滩水电站智能型大坝今封顶，300米高还抗震，新技术世界首创</h1><p><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\">我国是全球水电大国，三峡水电站是世界最大的水电站，排名第二的是巴西和巴拉圭的伊泰普水电站，但很快伊泰普水电站就不是世界第二了，因为我国的白鹤滩水电站即将建成，其装机容量高达1,600万千瓦，比伊泰普水电站还要高200万千瓦，就在今天(2021年5月31日)，白鹤滩水电站大坝正在顶部浇筑，代表着这座水电站的大坝工程即将完工。</span></p><p><img src=\"/public/uploads/20210730/1627629673629263.jpeg\" title=\"1627629673629263.jpeg\" alt=\"bf9f61ce3b4647a39b8f1b44f67d99cc.jpeg\"/></p><p><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\">大坝是水电站的</span><span style=\"background-color: rgb(255, 255, 255); color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px;\">核心建筑，承担着蓄水、调水、泄洪等的重要职能，通常也是水电站的主体建筑。白鹤滩水电站大坝由拦河坝、泄洪消能设施、引水发电系统等主要建筑物组成，拦河坝是大坝主体，为混凝土双曲拱坝，坝顶高程834米，最大坝高289米，看到这里可能不少朋友有点懵，其实坝顶高层834米指的是海拔高度，最大坝高289米是指大坝建筑物从底部到顶端的高度。</span><span style=\"background-color: rgb(255, 255, 255); color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px;\">白鹤滩水电站大坝的拱顶厚度为14米，基础最大厚度95米，坝顶弧长约209米，混凝土浇筑方量约803万立方米，工程量相当巨大，因此被网友们称之为是“坝天虎”(大坝中的“霸天虎”之意)。</span></p><p><img src=\"/public/uploads/20210730/1627629714722406.jpeg\" title=\"1627629714722406.jpeg\" alt=\"f9ce7dca325b48a8924de8d885e2c206.jpeg\"/></p><p><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\"><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\">建造这样的巨型大坝并非易事，施工过程中中，承建的三峡集团白鹤滩工程建设部的建设者们攻克了在高地震烈度区建300米级特高拱坝等世界性技术难题，如在大坝坝体内埋设上万支监测仪器，近8万米长的测温光纤，能感知坝体在浇筑混凝土化学反应中产生的温度和变形等重要信息，并反馈给智能建造信息管理平台，工作人员可以对各项系统准确进行智能控制和实时调节，实现建造运行全周期精细化管控，因此大坝具有了自检的智能功能，从开始浇筑之后就没有产生一条温度裂缝，该技术为世界首创，也标志着我国已掌握大体积混凝土温控防裂关键技术，必将进一步提升我国水电建设方面的核心竞争力，而白鹤滩水电站大坝也因为这项技术而被赞为“最聪明的大坝”。</span></span></p><p><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\"><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\"><img src=\"/public/uploads/20210730/1627629744222874.jpeg\" title=\"1627629744222874.jpeg\" alt=\"8896363e89c0427aa2be2594b8ba8506.jpeg\"/></span></span></p><p><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\"><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\"><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\">由于白鹤滩水电站位于我国西南地区的地震多发区，因此这座大坝非常讲究抗震功能，设计和建造得非常坚固，是世界上300米级高拱坝中抗震系数最高的大坝。</span></span></span></p><p><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\"><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\"><img src=\"/public/uploads/20210730/1627629783796518.jpeg\" title=\"1627629783796518.jpeg\" alt=\"5a71c6a27ada47209afb69c4da28d61b.jpeg\"/></span></span></p><p><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\"><br/></span></p><p><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\"><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\">白鹤滩水电站大坝于2017年4月开始浇筑，历经4年零两个月的施工，如今终于实现全线浇筑到顶，标志着大坝建造即将完工，也为即将到来的今年7月份首批机组发电打下了基础。</span></span></p><p><span style=\"background-color: rgb(255, 255, 255); color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px;\"></span></p><p><img src=\"/public/uploads/20210730/1627629847855711.jpeg\" title=\"1627629847855711.jpeg\"/></p><p><img src=\"/public/uploads/20210730/1627629847614940.jpeg\" title=\"1627629847614940.jpeg\"/></p><p><span style=\"background-color: rgb(255, 255, 255); color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px;\"><br/></span><br/></p><p><span style=\"background-color: rgb(255, 255, 255); color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px;\">白鹤滩水电站位于四川省凉山州宁南县和云南省昭通市巧家县境内，是我国在金沙江下游干流河段梯级开发的第二个梯级电站，具有发电、防洪、航运等功能，它将和上下游多级电站协调运作发挥综合效益。其水库正常蓄水位为825米(海拔)，库容量约206亿立方米，最大泄洪量为每秒42,348立方米。</span></p><p><span style=\"background-color: rgb(255, 255, 255); color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px;\"><img src=\"/public/uploads/20210730/1627629874348690.jpeg\" title=\"1627629874348690.jpeg\" alt=\"5cbc1e2a9f4a414e9612c32ded2daf31.jpeg\"/></span></p><p><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\"><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\"><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\">工程建造的地下洞室长度217公里，地下发电厂房装有16台100万千瓦的水力发电机组，初拟装机容量1600万千瓦，预估多年平均发电量为602.4亿千瓦时，相当于每年节约火电燃烧标准煤约1968万吨，减少排放二氧化碳约5160万吨。</span></span></span></p><p><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\"><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\"><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\">白鹤滩水电站也是全球正在建造中的最大的水电站，央视新闻称它至少有6项指标世界第一，分别是:水轮发电机组单机发电容量为100万千瓦，世界第一;地下洞室群的规模世界第一;圆筒式尾水调压室的规模世界第一;300米级高拱坝抗震参数为世界第一;无压泄洪洞群的规模世界第一;还有世界首次全坝使用低热水泥混凝土。该水电站建成之后其发电装机容量将仅次于三峡水电站，排名世界第二位，伊泰普水电站为第三，我国的溪洛渡水电站为第四位。</span></span></span></p><p><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\"><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\"><span style=\"color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; letter-spacing: 0.5px; background-color: rgb(255, 255, 255);\"><br/></span></span></span></p><p><img src=\"/public/uploads/20210730/1627629937958613.jpeg\" title=\"1627629937958613.jpeg\" alt=\"af2505a063f04b8998578176ac31fa29.jpeg\"/></p>','/public/uploads/20210730/ae930f0d37dbe453bd65966441e0a6a2.jpg',1627453511,0),(24,'华夏云投—海南新机场项目','海南新机场项目介绍','<h1 style=\"margin: 0px 0px 19px; padding: 0px; font-size: 36px; line-height: 48px;\">好消息!三亚新机场真的来了!</h1><p>&nbsp;<span style=\"margin-right: 10px;\">2019-09-11 17:28:22</span></p><p><br/></p><p>三亚新机场人工岛工程项目位于海南省三亚市红塘湾海域，距三亚市中心区27km，距现有三亚凤凰国际机场约15km，离岸约4km</p><p class=\"pi\" style=\"margin-top: 0px; margin-bottom: 28px; padding: 0px; text-align: center; line-height: 28px; font-family: Helvetica, Tahoma, Arial, &quot;PingFang SC&quot;, &quot;Microsoft YaHei&quot;, 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\">三亚新机场曝新消息，</p><p class=\"pi\" style=\"margin-top: 0px; margin-bottom: 28px; padding: 0px; text-align: center; line-height: 28px; font-family: Helvetica, Tahoma, Arial, &quot;PingFang SC&quot;, &quot;Microsoft YaHei&quot;, 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\">新机场人工岛工程首次环评公示，</p><p class=\"pi\" style=\"margin-top: 0px; margin-bottom: 28px; padding: 0px; text-align: center; line-height: 28px; font-family: Helvetica, Tahoma, Arial, &quot;PingFang SC&quot;, &quot;Microsoft YaHei&quot;, 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\">新机场人工岛工程包括</p><p class=\"pi\" style=\"margin-top: 0px; margin-bottom: 28px; padding: 0px; text-align: center; line-height: 28px; font-family: Helvetica, Tahoma, Arial, &quot;PingFang SC&quot;, &quot;Microsoft YaHei&quot;, 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\"><strong>三亚新机场人工岛主体工程</strong>和&nbsp;<strong>三亚新机场对外交通工程</strong>两部分。</p><p class=\"pi\" style=\"margin-top: 0px; margin-bottom: 28px; padding: 0px; line-height: 28px; font-family: Helvetica, Tahoma, Arial, &quot;PingFang SC&quot;, &quot;Microsoft YaHei&quot;, 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"color: rgb(51, 51, 51); text-indent: 2em;\">9月10日，三亚机场建设有限公司发布《三亚新机场人工岛工程环境影响评价》，</span><strong style=\"color: rgb(51, 51, 51); text-indent: 2em;\">三亚新机场人工岛工程项目位于海南省三亚市红塘湾海域，距三亚市中心区27km</strong><span style=\"color: rgb(51, 51, 51); text-indent: 2em;\">，距现有三亚凤凰国际机场约15km，离岸约4km。</span></p><p style=\"margin-top: 0px; margin-bottom: 28px; padding: 0px; text-indent: 2em; line-height: 28px; color: rgb(51, 51, 51); font-family: Helvetica, Tahoma, Arial, &quot;PingFang SC&quot;, &quot;Microsoft YaHei&quot;, 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\">三亚新机场人工岛工程包括三亚新机场人工岛主体工程和三亚新机场对外交通工程两部分。</p><p style=\"margin-top: 0px; margin-bottom: 28px; padding: 0px; text-indent: 2em; line-height: 28px; color: rgb(51, 51, 51); font-family: Helvetica, Tahoma, Arial, &quot;PingFang SC&quot;, &quot;Microsoft YaHei&quot;, 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\"><img src=\"/public/uploads/20210730/1627629534994235.png\" title=\"1627629534994235.png\" alt=\"0a28ee39-0ff9-4c9b-a8c7-b18df2b56b8a.png\"/></p><p style=\"margin-top: 0px; margin-bottom: 28px; padding: 0px; text-indent: 2em; line-height: 28px; color: rgb(51, 51, 51); font-family: Helvetica, Tahoma, Arial, &quot;PingFang SC&quot;, &quot;Microsoft YaHei&quot;, 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\">主体工程采用带圆角的矩形人工岛整体形态，用海总面积1697.1万m2，其中陆域形成面积1574.8万m2，护岸总长15601.6m，工程总工期5年，陆域填方量约46274.1万m3。</p><p style=\"margin-top: 0px; margin-bottom: 28px; padding: 0px; text-indent: 2em; line-height: 28px; color: rgb(51, 51, 51); font-family: Helvetica, Tahoma, Arial, &quot;PingFang SC&quot;, &quot;Microsoft YaHei&quot;, 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\">考虑到陆岛交通连接需求，三亚新机场人工岛项目配套建设三亚新机场对外交通工程，人工岛对外交通采用公路、轨道交通分开建设的跨海桥梁方案，并联建设两座跨海桥梁，桥梁起点位于人工岛东侧，公路、轨道交通桥梁长度均为6040m。</p><p><br/></p>','/public/uploads/20210730/faabcc2f2112c0c80bee8d7d20fe176c.jpg',1627454655,0),(25,'北京上德中心商业综合体项目','北京上德中心商业综合体项目介绍','<h1 class=\"title\" style=\"box-sizing: border-box; margin: 0px; font-size: 40px; font-family: &quot;Microsoft YaHei&quot;, 微软雅黑; line-height: 1.2; color: rgb(51, 51, 51); padding: 0px 0px 36px; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"font-size: 18px;\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <span style=\"font-size: 16px;\">上德</span><span style=\"font-size: 16px;\">中心计划2021年开业，北京南城再添全新商业综合体</span></span></h1><p><span style=\"font-size: 18px;\"><span style=\"font-size: 16px;\"><img src=\"/public/uploads/20210806/1628219425708694.png\" title=\"1628219425708694.png\" alt=\"202011617261456229x.png\"/></span></span></p><h1 class=\"title\" style=\"box-sizing: border-box; margin: 0px; font-size: 40px; font-family: &quot;Microsoft YaHei&quot;, 微软雅黑; line-height: 1.2; color: rgb(51, 51, 51); padding: 0px 0px 36px; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"font-size: 18px;\"></span><br/></h1><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; line-height: 34px; color: rgb(64, 64, 64); font-family: 微软雅黑, &quot;Arial Narrow&quot;, HELVETICA; white-space: normal;\">随着大兴机场的正式通航，北京南城特别是大兴区的发展在不断吸引着众多目光。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; line-height: 34px; color: rgb(64, 64, 64); font-family: 微软雅黑, &quot;Arial Narrow&quot;, HELVETICA; white-space: normal;\">　　目前，南城区域配套在加速升级，其区域商业发展也进入到了全新阶段，除了即将于年底亮相的北京大兴大悦春风里外，另外一个备受期待的全新商业综合体——上德中心，也将于2021年正式开业。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; line-height: 34px; color: rgb(64, 64, 64); font-family: 微软雅黑, &quot;Arial Narrow&quot;, HELVETICA; white-space: normal;\">　　据悉，上德中心是由格雷时尚商业地产版块的子公司北京上德置业有限公司倾力打造，以时尚产业为主导，集购物、办公、文化、休闲、体验于一体的商业综合体，仲量联行为其提供多维度商业地产服务。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; line-height: 34px; color: rgb(64, 64, 64); font-family: 微软雅黑, &quot;Arial Narrow&quot;, HELVETICA; white-space: normal;\">　　上德中心地处大兴国家新媒体产业基地。该基地前身是有着20多年历史的老工业区，近年来，该区域已然变身为集创业、孵化、服务、展示、投融资等多位一体的高端文化创意产业聚集区。</p><p><span style=\"font-size: 18px;\"><img src=\"/public/uploads/20210806/1628219458524793.png\" title=\"1628219458524793.png\" alt=\"2020116172631734116x.png\"/></span></p><p><span style=\"font-size: 18px;\"></span></p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; line-height: 34px; color: rgb(64, 64, 64); font-family: 微软雅黑, &quot;Arial Narrow&quot;, HELVETICA; white-space: normal;\">公开资料显示，上德中心以办公为核心，承载产业发展；以商业作为商务和城市配套功能，形成活力空间；以服装设计谷和联合办公为共享功能，为产业办公提供产业配套、为商业消费提供创意。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; line-height: 34px; color: rgb(64, 64, 64); font-family: 微软雅黑, &quot;Arial Narrow&quot;, HELVETICA; white-space: normal;\"><strong style=\"margin: 0px; padding: 0px;\">　　以人为本、开放融合的高品质办公空间</strong></p><p><img src=\"/public/uploads/20210806/1628219500886403.png\" title=\"1628219500886403.png\" alt=\"20201161727588575x.png\"/></p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; line-height: 34px; color: rgb(64, 64, 64); font-family: 微软雅黑, &quot;Arial Narrow&quot;, HELVETICA; white-space: normal;\">作为大兴地区国际甲级生态新地标，上德中心的写字楼版块由三栋独立塔楼构成7万平方米办公空间，强调一体化与“量身定制”的理念，通过多层次公共空间的塑造和完善的配套服务系统，为租户提供超一流的未来办公体验，目前已吸引科技、金融、教育、服装、文化传媒、IT互联网、地产等行业的多家知名企业。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; line-height: 34px; color: rgb(64, 64, 64); font-family: 微软雅黑, &quot;Arial Narrow&quot;, HELVETICA; white-space: normal;\"><strong style=\"margin: 0px; padding: 0px;\">　　充满活力、艺术多元的精致商业社区</strong></p><p><img src=\"/public/uploads/20210806/1628219529340388.png\" title=\"1628219529340388.png\" alt=\"202011617272331968x.png\"/></p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; line-height: 34px; color: rgb(64, 64, 64); font-family: 微软雅黑, &quot;Arial Narrow&quot;, HELVETICA; white-space: normal;\">在商业版块上，上德中心与国内知名商业品牌王府井奥莱强强联手，打造大兴首家品致奥莱，以良好的商誉基础与品牌资源，为大兴地区引入全新的生活方式和消费模式。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; line-height: 34px; color: rgb(64, 64, 64); font-family: 微软雅黑, &quot;Arial Narrow&quot;, HELVETICA; white-space: normal;\">　　近6万平方米的商业空间，完美融合时尚、艺术与文创元素，推出“优选梦工厂”的概念，精品商业、品牌体验店与奥莱工厂店交相呼应，必将打造北京南城令人瞩目的新生活方式打卡圣地</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; line-height: 34px; color: rgb(64, 64, 64); font-family: 微软雅黑, &quot;Arial Narrow&quot;, HELVETICA; white-space: normal;\">　　此外，为了强化上德中心的产业聚集能力，格雷时尚考虑将北京CED时尚产业创意中心、北京服装学院、清华美术学院教学实习及科研基地引入。同时，格雷时尚正在与欧洲设计学院进行深入对接，拟成立顶级服装学院并进驻上德中心，使得上德中心成为产、学、研、销一体化的时尚创意产业旗舰项目。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; line-height: 34px; color: rgb(64, 64, 64); font-family: 微软雅黑, &quot;Arial Narrow&quot;, HELVETICA; white-space: normal;\">　　上德中心工程总监卞春表示：作为国家新媒体产业基地的门户形象，上德中心从整体规划到公共空间、景观环境、建筑形象等均以超高品质为基准，将打造北京南城令人瞩目的多元新型商业综合体，同时也为文创、科技及创新产业类企业落户提供综合性解决方案。　　</p><p><br/></p>','/public/uploads/20210806/fe28b4f287569349f969c028087776f4.jpg',1628219551,0),(26,'香港国际机场第三跑道项目股权','香港国际机场第三跑道项目介绍','<h1 class=\"article__title\" style=\"transition: margin 0.1s linear 0s, padding 0.1s linear 0s, width 0.1s linear 0s, height 0.1s linear 0s; margin: 0px 0px 10px; overflow-wrap: break-word; color: rgb(64, 64, 64); letter-spacing: 1px; font-size: 20px; line-height: 30px; font-family: &quot;PingFang SC&quot;, &quot;Hiragino Sans GB&quot;, &quot;Microsoft YaHei&quot;, &quot;WenQuanYi Micro Hei&quot;, &quot;Helvetica Neue&quot;, Arial, sans-serif; white-space: normal; background-color: rgb(255, 255, 255);\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;香港国际为第三条跑道融资13亿美元</h1><p><img src=\"/public/uploads/20210806/1628220184904551.jpg\" title=\"1628220184904551.jpg\" alt=\"photo_2021-08-06_11-23-15.jpg\"/></p><p>在2020年的危机中，主要航空枢纽的航空流量直线下降。通常熙熙攘攘的主要机场也因客流量下降而变得安静。香港国际公司也不例外。尽管亚洲在某些方面略有下降，但随着国内交通量的增加，国际上基本上不存在。从4月初的最低点（流量下降了76％）起，香港国际机场已恢复了部分运力。</p><p>香港机场管理局坚信空中交通将恢复到2019年的水平并将恢复其增长，因此成功地筹集了13亿美元的资金，用于在机场建设第三条跑道。</p><p><img src=\"/public/uploads/20210806/1628220292398811.jpg\" title=\"1628220292398811.jpg\" alt=\"photo_2021-08-06_11-25-02.jpg\"/></p><p>&nbsp; <br/></p><p>这笔交易是该机构首次向美国金融家开放这项投资。该交易包括9亿美元的10年期债券和另外6亿美元的30年期债券。这些是有史以来发行时间最长的文件，对票据的需求被超额认购了六倍以上。</p><p>香港为何需要第三条跑道？</p><p>自2011年机场管理局制定“香港国际机场总体规划2030”以来，在香港国际机场建设第三条跑道的项目就一直在讨论中。该项目花费了数年时间来经历咨询和评估的各个阶段，并于2016年开始实施。</p><p><img src=\"/public/uploads/20210806/1628220383287486.jpg\" title=\"1628220383287486.jpg\" alt=\"t01311f8fa732de10cf.webp.jpg\"/></p><p>新跑道将与现有跑道平行并向北运行</p><p>除了对机场本身进行各种扩展和改善外，该项目的关键要素是增加第三条跑道。新跑道长3800米，与现有两条跑道的北侧平行。它被设置为仅适用于到达者，并预计将使空中交通的通行能力每小时增加33次。</p><p>尽管已有航空公司和政府给予了广泛支持，但该项目并非没有障碍。由于机场的位置，大约需要开垦650公顷的土地，通过将疏浚的沙子和深层水泥结合起来，以稳定泥泞的基底。</p><p>关于空域拥挤的问题也引起了人们的关注，深圳宝安国际机场增加了自己的第三条跑道，加剧了这种情况。如果从深圳使用向南起飞，它可能会与香港的新跑道争夺领空。</p><p>但是，鉴于建造工作至少要持续到2024年，并且预计届时空中交通量将恢复，因此人们对第三条跑道及时到达以促进香港航空发展的希望很高。</p><p><br/></p>','/public/uploads/20210806/9f0b1f049bdad63f2efe4703e81682c7.jpg',1628220408,0);
/*!40000 ALTER TABLE `article` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assignment_log`
--

DROP TABLE IF EXISTS `assignment_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assignment_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL COMMENT '转让的用户id',
  `to_uid` int NOT NULL COMMENT '接收的用户id',
  `product_id` int NOT NULL COMMENT '产品id',
  `tid` int NOT NULL COMMENT '原订单id',
  `new_id` int NOT NULL COMMENT '新订单id',
  `type` tinyint NOT NULL DEFAULT '1' COMMENT '订单类型 1货币 2返利',
  `create_time` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT COMMENT='转让记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignment_log`
--

LOCK TABLES `assignment_log` WRITE;
/*!40000 ALTER TABLE `assignment_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `assignment_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attachment`
--

DROP TABLE IF EXISTS `attachment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attachment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT '用户ID',
  `md5` varchar(50) NOT NULL COMMENT 'md5',
  `file_path` varchar(255) NOT NULL COMMENT '文件路径',
  `create_time` int NOT NULL COMMENT '上传时间',
  `file_size` int NOT NULL DEFAULT '0' COMMENT '文件大小',
  `file_name` varchar(64) NOT NULL DEFAULT '' COMMENT '文件名称',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `attachment_user_id_idx` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT COMMENT='附件表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attachment`
--

LOCK TABLES `attachment` WRITE;
/*!40000 ALTER TABLE `attachment` DISABLE KEYS */;
INSERT INTO `attachment` VALUES (2,1,'3c1ef6c8cefb09db50c4749c183aca98','/home/ai/work/stock/stock-new/public/uploads/202201/20220125211459Tl676.jpeg',1643116499,7299,'/uploads/202201/20220125211459Tl676.jpeg'),(6,1,'46d64e6208d2929fcc2c4fd2b19f75c8','/home/ai/work/stock/stock-new/public/uploads/202201/20220125212950TGLnU.jpg',1643117390,130755,'/uploads/202201/20220125212950TGLnU.jpg'),(7,1,'dcf686e58654ed02338029c37b69e2a5','/home/ai/work/stock/stock-new/public/uploads/202201/20220125220645TvX2S.jpeg',1643119605,7299,'/uploads/202201/20220125220645TvX2S.jpeg'),(8,1,'36d32a0baecec5a511b584357e7c9c05','/home/ai/work/stock/stock-new/public/uploads/202201/20220125220701TuNiF.jpeg',1643119621,7299,'/uploads/202201/20220125220701TuNiF.jpeg'),(9,1,'b11eadeb67560e22975676a140b4a748','/home/ai/work/stock/stock-new/public/uploads/202201/20220129135730TIJQb.jpg',1643435850,130755,'/uploads/202201/20220129135730TIJQb.jpg'),(10,1,'3fdb0d18606a129cddb99bf785387294','/home/ai/work/stock/stock-new/public/uploads/202201/20220129151747TdV3m.jpeg',1643440667,7299,'/uploads/202201/20220129151747TdV3m.jpeg'),(11,1,'af05d786ef3e81893a8dd456228d5764','/home/ai/work/stock/stock-new/public/uploads/202202/20220206095652THMqF.jpg',1644112612,33973,'/uploads/202202/20220206095652THMqF.jpg'),(12,1,'2b510a31b7c99ae4cf1d814d54f4ddae','/home/ai/work/stock/stock-new/public/uploads/202202/20220206095755TqVoh.jpeg',1644112675,7299,'/uploads/202202/20220206095755TqVoh.jpeg');
/*!40000 ALTER TABLE `attachment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banner`
--

DROP TABLE IF EXISTS `banner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banner` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT 'banner标题',
  `uri` varchar(255) NOT NULL DEFAULT '' COMMENT 'banner跳转地址',
  `images` varchar(255) NOT NULL DEFAULT '' COMMENT 'banner图片地址',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '状态',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banner`
--

LOCK TABLES `banner` WRITE;
/*!40000 ALTER TABLE `banner` DISABLE KEYS */;
INSERT INTO `banner` VALUES (20,'2','asdfsaf','/uploads/202201/20220125220645TvX2S.jpeg',1628478897,1,1643119607),(22,'3','asdfsadf','/uploads/202201/20220125220701TuNiF.jpeg',1632117615,1,1643119622),(23,'1','asfasdfasfd','/uploads/202201/20220125212950TGLnU.jpg',1632291440,1,1643119593);
/*!40000 ALTER TABLE `banner` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `channel`
--

DROP TABLE IF EXISTS `channel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `channel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否展示，1展示，2不展示',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '栏目名称',
  `url` varchar(255) DEFAULT '' COMMENT '跳转地址',
  `type` tinyint NOT NULL DEFAULT '0' COMMENT '栏目类型 1是商品类，2是独立页，3基金类型，4股权，5纪念币',
  `images` varchar(255) DEFAULT '' COMMENT '栏目图片',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `p_id` int NOT NULL DEFAULT '0' COMMENT '父级id',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '栏目描述',
  `content` text,
  `sorts` int NOT NULL DEFAULT '0' COMMENT '排序值越小越靠前',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `channel_is_show_idx` (`is_show`),
  KEY `channel_type_idx` (`type`),
  KEY `channel_sorts_idx` (`sorts`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `channel`
--

LOCK TABLES `channel` WRITE;
/*!40000 ALTER TABLE `channel` DISABLE KEYS */;
INSERT INTO `channel` VALUES (1,2,'货币专区','/product/index/channel_id/1',1,'/static/picture/5bff97ffcc002.png',1551342249,0,'限时发',NULL,0,0),(2,2,'理财热销推荐','/product/zq/channel_id/2',3,'/static/picture/5bff97ffcc002.png',1551342730,0,'标期灵活 | 多样选择',NULL,1,0),(3,2,'股权热销推荐','product/zq/channel_id/4',4,'/static/picture/5bff97ffcc002.png',1552788519,0,'股权专区',NULL,6,0),(5,1,'推广连接','/user/spread',2,'/static/picture/5bff97ffcc002.png',1551342901,0,'','<p>&nbsp; &nbsp;</p>',3,0),(6,2,'领取红包','/user/hongbao',2,'/static/picture/5bff97ffcc002.png',1551342932,0,'',NULL,2,0),(7,2,'幸运转盘','/user/welfare',2,'/static/picture/5bff97ffcc002.png',1551342974,0,'幸运大转盘',NULL,3,0),(8,2,'操作指南','/about/about',2,'/static/picture/5bff97ffcc002.png',1551343004,0,'','<p><span style=\"color: rgb(14, 23, 38); font-family: &quot;PingFang SC&quot;, -apple-system, BlinkMacSystemFont, Roboto, &quot;Helvetica Neue&quot;, Helvetica, Arial, &quot;Hiragino Sans GB&quot;, &quot;Source Han Sans&quot;, &quot;Noto Sans CJK Sc&quot;, &quot;Microsoft YaHei&quot;, &quot;Microsoft Jhenghei&quot;, sans-serif; font-size: 14px;\"><span style=\" background-color: rgb(255, 255, 255);\"><strong></strong></span></span></p><p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</p><p><span style=\"font-size: 18px;\">1.&nbsp;如何实名认证和绑定银行卡？</span></p><p><span style=\"font-size:18px;\">&nbsp;&nbsp;点击----我的----设置----实名认证----按照要求填写实名认证信息认证。</span></p><p><span style=\"font-size:18px;\">&nbsp;&nbsp;点击----我的----设置----我的银行卡----添加银行卡----按照要求正确填写银行卡信息（注意：银行卡名字和收款卡号必须是同一个人）----立即绑定。</span></p><p><span style=\"font-size:18px;\">&nbsp;</span></p><p><span style=\"font-size:18px;\">2.&nbsp;如何购买股权、基金、货币等理财产品？</span></p><p><span style=\"font-size:18px;\">&nbsp;&nbsp;点击APP下面的股权、理财、货币菜单选项，进入对应的股权、货币、基金专区进行购买。购买需要先核对你想要购买的数量和金额，再进行购买，购买金额和转账金额必须一致。</span></p><p><span style=\"font-size:18px;\">&nbsp;</span></p><p><span style=\"font-size:18px;\">3.&nbsp;如何完成付款？</span></p><p><span style=\"font-size:18px;\">&nbsp;&nbsp;付款方式有两种：</span></p><p><span style=\"font-size:18px;\">&nbsp;&nbsp;第一种：银行卡支付。点击立即购买，进入银行卡转账，按照APP上的收款账号，通过手机银行进行转账，或者通过支付宝转账到银行卡。转账完成后保存好正确的转账截图（转账凭证需要有收款人名字、收款人卡号以及转账时间，方便财务核实你的转账信息）转账完成后，再回到APP银行卡转账页面，点击摄像头图案，选择转账截图图片，上传转账凭证，等提交栏变成红色，图片上传成功，点击提交就可以了。订单提交后，会由审核部门负责订单审核，确认订单无误，审核通过后，你购买的产品就会到账。同一订单只需要提交一次，不用重复上传；</span></p><p><span style=\"font-size:18px;\">&nbsp;&nbsp;第二种：余额支付。选择你要购买的产品，支付方式选择余额支付，点击立即购买，输入支付密码，确认。付款成功就说明购买成功了。余额支付购买的产品立即到账，不用上传截图。注意：余额支付是指你在华夏云投账户的余额，余额来自于你的推荐佣金、股权和基金的每天返利。为了大家能够获得更大的收益，大家也可以用账户余额资金来进行股权和基金的复投，简单便捷。</span></p><p><span style=\"font-size:18px;\">&nbsp;</span></p><p><span style=\"font-size:18px;\">4.&nbsp;如何查看我购买的产品？</span></p><p><span style=\"font-size:18px;\">&nbsp;&nbsp;点击我的，在我的股权、我的理财和我的货币可以查看对应的产品。</span></p><p><span style=\"font-size:18px;\">&nbsp;</span></p><p><span style=\"font-size:18px;\">5.&nbsp;如何提现？</span></p><p><span style=\"font-size:18px;\">&nbsp;点击----我的---我的钱包---账户余额栏的资金是你可以提现的资金。点击提现----输入你要提现的金额---选择你要提交的银行卡，确定提现。提现申请提交后，会由财务进行审核，审核通过后你的提现资金即可到账。</span></p><p><span style=\"color: rgb(14, 23, 38); font-family: &quot;PingFang SC&quot;, -apple-system, BlinkMacSystemFont, Roboto, &quot;Helvetica Neue&quot;, Helvetica, Arial, &quot;Hiragino Sans GB&quot;, &quot;Source Han Sans&quot;, &quot;Noto Sans CJK Sc&quot;, &quot;Microsoft YaHei&quot;, &quot;Microsoft Jhenghei&quot;, sans-serif; font-size: 14px;\"><span style=\"background-color: rgb(255, 255, 255);\"></span><br /></span></p>',0,0),(15,2,'疑问解答','/user/qa',2,'/static/picture/5bff97ffcc002.png',1553052500,0,'疑问解答','<div><p>1.我在云计划平台上投资安全吗？<br />本平台采用多种资金管理措施，竭力保障用户的资金安全：&nbsp;1.专业第三方支付机构提供资金支付服务：公司委托专业的、具有支付业务资质第三方机构提供用户资金充值、取现等资金支付服务。&nbsp;2.唯一取现银行卡控制：用户在平台投资前须进行实名认证并绑定唯一一张属于本人的银行卡，投资本金收益仅可取现至该绑定银行卡中，从而保证平台资金封闭运行。&nbsp;3.全网站系统加密及保护技术：采用国际通用的系统加密及数据保护技术，保障用户在访问网站过程资金、密码等数据信息的安全&nbsp;4.资金管理内部控制严格：我们秉承专业金融机构的资金管理成熟经验和流程，实行严格的审核机制，以保障用户资金安全。云计划平台所有资金由银行进行监管，确保所有用户的资金安全。<br /><br /><br /><br />2.电子合同和股权证书是不是具有法律效应？</p><p>您好！根据《合同法》和《电子商务示范法》，在您点下投資按钮的时候就真实明确表达了您的投资意愿，所以您从网站上下载的合同具有法律效力。电子股权证书具有同等法律效力。</p><p><br /></p><p>3.在平台充值多久能到账？</p><p>平台充值审核时间是早上九点到晚上十二点，所有充值订单在三个小时内会审核通过。</p><p><br /></p><p>4.平台的提现时间和提现金额？</p><p>500元起就可以提现，提现时间是早上九点到晚上九点，所有提现24小时内到账。</p><p><br /></p><p><br /></p><p><br /></p><br /></div><div><br /></div>',1,0),(17,2,'联系我们','/user/concat',2,'/static/picture/5bff97ffcc002.png',1553137432,0,'云数E家客服李斌蝙蝠号：279700','<p></p><p>云计划客服李薇 畅聊ID：9286969&nbsp; 搜索添加好友，或者用畅聊APP扫码添加我的好友。&nbsp;&nbsp;</p><p>为了方便您联系我们，请务必添加我的畅聊ID，有疑问随时与我们联系。<br /></p><p><br /><br /></p><p><br /></p>',0,0),(18,2,'线下充值','/user/xianxiarec',2,'/static/picture/5bff97ffcc002.png',1553579012,0,'','<p><img src=\"/public/uploads/20190509/f993d8c8031e877ac1bbd091d884fb01.jpg\" alt=\"\" /><br /></p><p><br /></p>',0,0),(20,2,'安全保障','/detail/anquan',2,'/static/picture/5bff97ffcc002.png',1553588315,0,'','<span style=\"font-family: arial, 宋体, sans-serif; font-size: 14px; text-indent: 28px;\"></span>&nbsp; &nbsp;',0,0),(21,2,'支付宝','/user/zhifubaorec',2,'/static/picture/5bff97ffcc002.png',1556891864,0,'','<img src=\"/public/uploads/20190508/a032d4e5f18bc2d5587571e9709c943b.jpg\" alt=\"\" />',0,0),(22,2,'纪念币专区','/product/index/channel_id/22',5,'/static/picture/5bff97ffcc002.png',1624950749,0,'纪念币专区',NULL,0,0),(23,1,'关于我们','user/aboutus',2,'/public/uploads/20210905/3b9b7f37a71dd908368f3dbbf3fcd98e.png',1630822262,0,'','<p><img src=\"/public/uploads/20210908/13e04c22dbe30b361fdcab10f70acaf6.jpg\" alt=\"\" /><br /></p>',0,0),(24,1,'','',0,'',1634216398,0,'','<p></p>云计划客服李微&nbsp; 微核号：20440894&nbsp; 为了方便您联系我们，请务必添加我的微核号，有疑问随时联系我',0,0),(25,1,'asdfsadf','asdfsadf',1,'/uploads/202201/20220129151747TdV3m.jpeg',1643440657,0,'asdfasdf','asdf',0,1643440669);
/*!40000 ALTER TABLE `channel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `goods`
--

DROP TABLE IF EXISTS `goods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `goods` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` int NOT NULL COMMENT '用户UID',
  `product_id` int NOT NULL COMMENT '产品ID',
  `trade_no` varchar(100) NOT NULL COMMENT '订单号',
  `img` varchar(255) NOT NULL COMMENT '转账凭证',
  `money` double(20,2) NOT NULL COMMENT '金额',
  `sz_money` double(20,2) NOT NULL COMMENT '总市值',
  `buy_money` double(11,2) NOT NULL COMMENT '购买单份金额',
  `number` double(11,5) NOT NULL COMMENT '数量',
  `message` varchar(255) DEFAULT NULL COMMENT '备注',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0已付款1拒绝2审核中3未传凭证4已转他人',
  `pay_type` int DEFAULT NULL COMMENT '支付类型：1余额，2支付宝3银行',
  `get_method` int DEFAULT '1' COMMENT '获取方式，1购买2后台添加3购买商品赠送4他人转让',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '最后更新',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `i_goods_uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `goods`
--

LOCK TABLES `goods` WRITE;
/*!40000 ALTER TABLE `goods` DISABLE KEYS */;
/*!40000 ALTER TABLE `goods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `income_expenses_log`
--

DROP TABLE IF EXISTS `income_expenses_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `income_expenses_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户ID号',
  `money` double(20,2) NOT NULL COMMENT '收支金额',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '收入支出标题',
  `create_time` datetime DEFAULT NULL COMMENT '时间',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1是返利，2购买产品，3分佣，4充值，5用户提现，6现金红包，7理财金, 8手机充值，9注册礼金，10扣款，11原始股权，12扣款（扣可提）',
  `order_id` int NOT NULL DEFAULT '0' COMMENT '订单的ID号，只有返利的时候才有',
  `product_id` int NOT NULL COMMENT '产品ID',
  `to_uid` int DEFAULT '0' COMMENT '下级uid',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `i_income_log_uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `income_expenses_log`
--

LOCK TABLES `income_expenses_log` WRITE;
/*!40000 ALTER TABLE `income_expenses_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `income_expenses_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `income_log`
--

DROP TABLE IF EXISTS `income_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `income_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `today_income` double(20,2) NOT NULL COMMENT '今日累计收益',
  `today_date` int NOT NULL COMMENT '今日时间戳',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uid_today_date` (`uid`,`today_date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `income_log`
--

LOCK TABLES `income_log` WRITE;
/*!40000 ALTER TABLE `income_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `income_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu`
--

DROP TABLE IF EXISTS `menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT '' COMMENT '菜单名称',
  `parent_id` int unsigned NOT NULL DEFAULT '0' COMMENT '上级菜单',
  `method` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '方法 1:GET, 2:POST, 3:ALL',
  `url` varchar(200) NOT NULL DEFAULT '' COMMENT '链接地址',
  `is_blank` tinyint unsigned DEFAULT '0' COMMENT '是否新窗',
  `is_view` tinyint unsigned DEFAULT '0' COMMENT '是否显示',
  `state` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `level` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT '图标',
  `remark` varchar(200) NOT NULL DEFAULT '' COMMENT '备注',
  `created` int unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1408 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu`
--

LOCK TABLES `menu` WRITE;
/*!40000 ALTER TABLE `menu` DISABLE KEYS */;
INSERT INTO `menu` VALUES (1000,'快捷访问',0,1,'#2',0,1,1,1,99000,'layui-icon-home','BB寺你',0,1640879587),(1018,'网站管理',0,1,'#',0,0,1,1,75000,'layui-icon-website','',0,1640880335),(1115,'内容管理',0,1,'#',0,0,1,1,50000,'layui-icon-list','',0,1643093718),(1143,'审核管理',0,1,'#',0,0,1,1,30000,'layui-icon-auz','',0,1640880664),(1151,'权限管理',0,1,'#',0,0,1,1,80000,'layui-icon-group','',0,1640879566),(1177,'货币管理',0,1,'#',0,0,1,1,60000,'layui-icon-dollar','',0,1640880470),(1199,'产品管理',0,1,'#',0,0,1,1,70000,'layui-icon-app','',0,1640880426),(1220,'抽奖管理',0,1,'#',0,0,1,1,20000,'layui-icon-gift','',0,1640880516),(1239,'系统管理',0,1,'#',0,0,1,1,90000,'layui-icon-windows','',0,1640879554),(1374,'会员管理',0,1,'#',0,1,1,1,40000,'layui-icon-user','',0,1640880543),(1380,'菜单管理',1151,0,'/menus',0,0,1,2,1000,'','',0,0),(1381,'角色管理',1151,0,'/roles',0,0,1,2,2000,'','',0,0),(1382,'后台用户',1151,0,'/admins',0,0,1,2,1500,'','',0,0),(1383,'系统设置',1239,0,'/configs',0,0,1,2,9000,'','',0,1641215146),(1384,'附件管理',1239,0,'/attachments',0,0,1,2,8000,'','',0,1641016779),(1385,'版本管理',1239,0,'/version_upgrades',0,0,1,2,6000,'','',0,1641006544),(1386,'后台首页',1000,0,'/index/right',0,0,1,2,9000,'','',0,0),(1387,'轮播管理',1018,0,'/banners',0,0,1,2,9000,'','',0,0),(1388,'栏目管理',1018,0,'/channels',0,0,1,2,8000,'','',0,1641019578),(1389,'理财专区',1199,0,'/products/finances',0,0,1,2,9000,'','',0,1641039084),(1390,'股权专区',1199,0,'/products/rights',0,0,1,2,8000,'','',0,1641039101),(1391,'货币专区',1177,0,'/products/currencies',0,0,1,2,9000,'','',0,1641040209),(1392,'网站公告',1115,0,'/notices',0,0,1,2,9000,'','',0,0),(1393,'会员列表',1374,0,'/users',0,0,1,2,9000,'','',0,0),(1394,'提现管理',1374,0,'/user_withdraws',0,0,1,2,8000,'','',0,1641203973),(1395,'返利订单',1374,0,'/orders',0,0,1,2,7000,'','',0,1641213163),(1396,'货币订单',1374,0,'/goods',0,0,1,2,6000,'','',0,1641213175),(1397,'充值管理',1374,0,'/recharges',0,0,1,2,5000,'','',0,0),(1398,'奖品设置',1220,0,'/rewards',0,0,1,2,9000,'','',0,1641032823),(1399,'中奖记录',1220,0,'/reward_logs',0,0,1,2,7000,'','',0,1641033550),(1400,'返利审核',1143,0,'/orders/audits',0,0,1,2,9000,'','',0,1641214779),(1401,'货币审核',1143,0,'/goods/audits',0,0,1,2,8000,'','',0,1641213916),(1402,'充值审核',1143,0,'/recharges/audits',0,0,1,2,6000,'','',0,1641213904),(1403,'用户日志',1374,0,'/user_logs',0,0,1,2,2000,'','用户日志',1642490837,1642490837),(1404,'授权访问',1151,0,'/permission_ips',0,0,1,2,1800,'','',1642737400,1642737449),(1405,'用户消息',1115,0,'/user_messages',0,0,1,2,2000,'','',1643093914,1643093914),(1406,'报表管理',0,0,'/reports',0,0,1,1,10000,'layui-icon-table','报表管理',1644243826,1644243826),(1407,'佣金报表',1406,0,'/report_commissiions',0,0,1,2,9000,'','',1644243881,1644243881);
/*!40000 ALTER TABLE `menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `message` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '用户di',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '消息标题',
  `content` text COMMENT '消息内容',
  `type` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '1是返利，2购买产品，3分佣，4充值，5用户提现，6现金红包，7理财金, 8手机充值,9注册现金礼,10审核拒绝,11vip',
  `is_read` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否阅读，0否，1是',
  `create_time` int unsigned NOT NULL DEFAULT '0' COMMENT '发送时间',
  `product_id` int NOT NULL DEFAULT '0' COMMENT '产品编号',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '状态变更时间',
  `user_name` varchar(32) NOT NULL DEFAULT '' COMMENT '用户名称',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `i_message_uid` (`uid`),
  KEY `message_product_id_idx` (`product_id`),
  KEY `message_product_type_idx` (`type`),
  KEY `message_product_is_read_idx` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message`
--

LOCK TABLES `message` WRITE;
/*!40000 ALTER TABLE `message` DISABLE KEYS */;
/*!40000 ALTER TABLE `message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mobile`
--

DROP TABLE IF EXISTS `mobile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mobile` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `mobile` varchar(11) NOT NULL DEFAULT '' COMMENT '手机号码',
  `money` double(20,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '充值金额',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态，0待审核，1通过，2拒绝',
  `create_time` int unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mobile`
--

LOCK TABLES `mobile` WRITE;
/*!40000 ALTER TABLE `mobile` DISABLE KEYS */;
/*!40000 ALTER TABLE `mobile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notice`
--

DROP TABLE IF EXISTS `notice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notice` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '公告标题',
  `content` text NOT NULL COMMENT '公告内容',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '发布时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '最后修改',
  `is_show` tinyint NOT NULL DEFAULT '1' COMMENT '是否显示',
  `admin_id` int NOT NULL DEFAULT '0' COMMENT '修改用户编号',
  `admin_anme` varchar(64) NOT NULL DEFAULT '' COMMENT '修改用户名称',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `notice_title_idx` (`title`),
  KEY `notice_is_show_idx` (`is_show`),
  KEY `notice_admin_id_idx` (`admin_id`),
  KEY `notice_update_time_idx` (`update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notice`
--

LOCK TABLES `notice` WRITE;
/*!40000 ALTER TABLE `notice` DISABLE KEYS */;
INSERT INTO `notice` VALUES (1,'购买股权5倍分红活动延迟到17日晚24:00！','<p><span style=\"font-size: 14px;\"></span></p><p>由于财务提现处理不及时，导致很多家人提现没有及时到账以至于没有领取到重大福利集团特决定将购买股权买一送四，五倍分红的活动延迟至17日晚上24点。活动期间所有股权买一送四，永久享受五倍分红。<br/></p><p><span style=\";font-family:宋体;font-size:14px\"><span style=\"font-family:宋体\">例：购买</span><span style=\"font-family:Calibri\">20000</span><span style=\"font-family:宋体\">元生物医学新技术股权，系统将直接赠送</span><span style=\"font-family:Calibri\">80000</span><span style=\"font-family:宋体\">元生物医学新技术股权，实际到账</span><span style=\"font-family:Calibri\">100000</span><span style=\"font-family:宋体\">元生物医学新技术股权，分红由每天</span><span style=\"font-family:Calibri\">620</span><span style=\"font-family:宋体\">元，直接提升到每天分红</span><span style=\"font-family:Calibri\">3100</span><span style=\"font-family:宋体\">元。涨幅高达五倍。</span></span></p><p><span style=\";font-family:宋体;font-size:14px\"><span style=\"font-family:宋体\">活动时间：</span><span style=\"font-family:Calibri\">11</span><span style=\"font-family:宋体\">月</span><span style=\"font-family:Calibri\">16</span><span style=\"font-family:宋体\">日早上9点延长至17日晚上24点!</span></span></p><p style=\"margin-top:5px;margin-right:0;margin-bottom:5px;margin-left:0;text-indent:0\"><span style=\";font-family:宋体;font-size:16px\">选择比努力更重要，把握机会，成就梦想！</span><span style=\"font-family: 宋体;letter-spacing: 0;font-size: 14px\">让我们携手同行，一起实现中国梦！</span></p><p><span style=\";font-family:宋体;font-size:14px\"></span><br/></p><p><span style=\"font-size: 14px;\"></span><br/></p><p><br/></p>',1627447500,0,1,0,'');
/*!40000 ALTER TABLE `notice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order`
--

DROP TABLE IF EXISTS `order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id编号',
  `product_id` int NOT NULL DEFAULT '0' COMMENT '产品ID编号',
  `trade_no` varchar(255) NOT NULL DEFAULT '' COMMENT '订单编号',
  `img` varchar(255) NOT NULL COMMENT '转账凭证',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单状态，0正在返利，1返利结束,2审核中，3未传凭证,4拒绝',
  `money` double(20,2) NOT NULL DEFAULT '0.00' COMMENT '订单金额',
  `message` varchar(255) DEFAULT NULL COMMENT '备注',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `end_time` int DEFAULT '0' COMMENT '产品结束返利的时间戳',
  `fl_time` int NOT NULL DEFAULT '0' COMMENT '上次返利的时间',
  `pay_type` int NOT NULL COMMENT '支付类型1余额2支付宝3银行',
  `get_method` int DEFAULT '1' COMMENT '获取方式，1购买2后台添加3购买商品赠送',
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT '用户名称',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '最后修改',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `i_order_uid` (`uid`),
  KEY `i_order_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order`
--

LOCK TABLES `order` WRITE;
/*!40000 ALTER TABLE `order` DISABLE KEYS */;
/*!40000 ALTER TABLE `order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_ip`
--

DROP TABLE IF EXISTS `permission_ip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permission_ip` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip` varchar(64) NOT NULL DEFAULT '0' COMMENT '授权IP',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '状态',
  `remark` varchar(256) NOT NULL DEFAULT '0' COMMENT '备注 ',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_ip_ip_idx` (`ip`),
  KEY `permission_ip_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_ip`
--

LOCK TABLES `permission_ip` WRITE;
/*!40000 ALTER TABLE `permission_ip` DISABLE KEYS */;
INSERT INTO `permission_ip` VALUES (1,'127.0.0.1',1,'测试',1642737495,1642737555);
/*!40000 ALTER TABLE `permission_ip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `channel_pid` int NOT NULL DEFAULT '0',
  `channel_id` int NOT NULL DEFAULT '0' COMMENT '所属栏目',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '投资方式，1为小时，2为天',
  `term` int NOT NULL DEFAULT '0' COMMENT '投资期限，单位根据type来定',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '产品名称',
  `images` text NOT NULL,
  `desc` text,
  `end_time` datetime DEFAULT NULL COMMENT '产品结束时间',
  `aims_money` double(11,2) NOT NULL DEFAULT '0.00' COMMENT '目标金额',
  `least_money` double(11,2) NOT NULL DEFAULT '0.00' COMMENT '起投金额',
  `input_money` int NOT NULL DEFAULT '0' COMMENT '已认购金额',
  `content` text COMMENT '项目介绍',
  `plan` text COMMENT '投资方案',
  `proportion` double(11,2) NOT NULL DEFAULT '0.00' COMMENT '分成比例',
  `create_time` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态，1待开放，2开放中，3已满额',
  `sub_qc` int NOT NULL DEFAULT '0' COMMENT '起购份数',
  `pattern` tinyint(1) NOT NULL DEFAULT '1' COMMENT '返利方式，1连本带息，2本金利息同时返',
  `number` int NOT NULL DEFAULT '0' COMMENT '产品认购份数',
  `p_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '产品类型1：货币，2基金，3股权，4纪念币',
  `contract` mediumtext COMMENT '产品合同',
  `is_give` int DEFAULT '0' COMMENT '开启赠送，1开启',
  `give_type` int DEFAULT '0' COMMENT '赠送类型，3股权1货币2基金',
  `give_product_id` int DEFAULT '0' COMMENT '赠送产品ID',
  `give_power` decimal(3,2) DEFAULT '0.00' COMMENT '赠送倍率',
  `is_recommend` tinyint DEFAULT '0' COMMENT '是否推荐',
  `jindu` int NOT NULL DEFAULT '0' COMMENT '认购进度',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product`
--

LOCK TABLES `product` WRITE;
/*!40000 ALTER TABLE `product` DISABLE KEYS */;
INSERT INTO `product` VALUES (2,2,0,2,100,'张健爱心扶贫基金','[{\"name\":\"5533ca9350b0492f9e035b0a8962cfc5.png\",\"url\":\"\\/public\\/uploads\\/20210920\\/7da6b574dd56d0f14d70bd89a04a42c7.png\",\"uid\":\"1632119847819\",\"status\":\"success\"}]','','2022-02-17 18:03:57',999999999.00,200.00,3936200,'本项目募集资金将全部用于开春高速公路项目的建设、维护与开发。基金自购买成功后，第二天开始产生固定收益，基金持有期间享受每天返利。基金购买成功三个月内不可赎回本金，每天分红，分红可以提现，三个月后可以继续持有基金，继续享受每天返利；或者联系客服申请赎回，基金本金赎回后，返利停止。基金不赎回，享受永久返利！<br /><br /><br /><pre style=\"overflow-wrap: break-word; white-space: pre-wrap;\"></pre>','',1.80,1618664234,3,1,1,9999999,2,'',0,1,33,0.00,0,100,0),(3,3,0,2,100,'农业新技术股权','[{\"name\":\"农业新技术.jpg\",\"url\":\"\\/public\\/uploads\\/20210916\\/d6c99789cde840ea6ed2c800edc9c288.jpg\",\"uid\":\"1631795042243\",\"status\":\"success\"}]','','2022-02-24 09:04:55',999999999.00,15.00,5535000,'','',2.30,1618666003,2,100,1,999999,3,'<pre style=\"overflow-wrap: break-word; white-space: pre-wrap;\"><span style=\"font-family: Arial, Helvetica, sans-serif;\"></span></pre><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\"></span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">证书编号：##num##</span><br /></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace;\"><span style=\"white-space: pre-wrap;\">股东姓名：</span></span><span style=\"color: rgb(34, 34, 34); font-family: &quot;Microsoft YaHei&quot;;\"><span style=\"white-space: nowrap;\">##party_b##</span></span></p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">身份证号：</span><span style=\"color: rgb(34, 34, 34); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap;\">##idcard##</span><br /><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">股权数量：<span style=\"color: rgb(34, 34, 34); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap;\">##gushu##</span></span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\"><span style=\"color: rgb(34, 34, 34); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap;\"><br /></span></span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\"><strong><span style=\"font-size:18px;\">注意事项</span></strong></span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\"><span style=\"font-size:12px;\">1、本证是特有公司股份的唯一有效证明。</span></span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\"><span style=\"font-size:12px;\">2、本证需要妥善保管，不要外传。</span></span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\"><span style=\"font-size:12px;\">3、本证不得私自转让，转让无效。</span></span></p><p><br /></p><p><span style=\"font-size:12px;\">发证日期：##create_time##</span><br /></p><p><br /></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\"><br /></span></p><div style=\"top: 0px;\"><pre style=\"overflow-wrap: break-word; white-space: pre-wrap;\"></pre></div>',1,3,3,4.00,0,88,0),(19,1,0,2,0,'云计划数字货币10元','[{\"name\":\"10.jpg\",\"url\":\"\\/public\\/uploads\\/20210924\\/0eca10d4d77e5c3bd5a1bc006d42a602.jpg\",\"uid\":\"1632464976897\",\"status\":\"success\"}]','','2021-10-03 22:04:55',10.00,7.20,53269,'华夏云投所有货币，会由集团公司按照回收当天的市值统一现金回收！回收周期为两个月一次，第一批的回收日期是10月1日至10月7日！想要卖出货币的会员，只需要在回收日期内联系客服进行回收即可，回收资金当天可到账！货币具有升值功能，货币没有分红。<br /><br />','',0.00,1620455976,3,100,0,2147483647,1,'',0,1,19,0.00,0,0,1642670501),(26,2,0,2,100,'张健住房保障基金','[{\"name\":\"1539130595138.jpeg\",\"url\":\"\\/public\\/uploads\\/20210920\\/7239ab22b4a3a6b950250b1e78d9b0d4.jpeg\",\"uid\":\"1632120844911\",\"status\":\"success\"}]','','2022-02-23 00:09:11',999999999.00,1000.00,1162000,'本项目募集资金将全部用于舟岱跨海大桥项目的建设、维护与开发。基金自购买成功后，第二天开始产生固定收益，基金持有期间享受每天返利。基金购买成功三个月内不可赎回本金，每天分红，分红可以提现，三个月后可以继续持有基金，继续享受每天返利；或者联系客服申请赎回，基金本金赎回后，返利停止。基金不赎回，享受永久返利！<br /><br /><br /><pre style=\"overflow-wrap: break-word; white-space: pre-wrap;\"></pre>','',2.20,1618664234,2,1,1,889999999,2,'',0,1,33,0.00,1,56,0),(27,2,0,2,100,'张健抗新冠疫情基金','[{\"name\":\"6a600c338744ebf8157f6946467ab22c6159a7c2.jpeg\",\"url\":\"\\/public\\/uploads\\/20210916\\/de3226de2fca7b1629085eb12d63d412.jpeg\",\"uid\":\"1631795721016\",\"status\":\"success\"}]','','2022-02-17 20:31:20',999999999.00,2000.00,450000,'本项目募集资金将全部嘉兴海上1号风电项目的建设、维护与开发。基金自购买成功后，第二天开始产生固定收益，基金持有期间享受每天返利。基金购买成功三个月内不可赎回本金，每天分红，分红可以提现，三个月后可以继续持有基金，继续享受每天返利；或者联系客服申请赎回，基金本金赎回后，返利停止。基金不赎回，享受永久返利！<br /><br /><br /><pre style=\"overflow-wrap: break-word; white-space: pre-wrap;\"></pre>','',2.50,1618664234,2,1,1,889999,2,'',0,1,33,0.00,0,57,0),(28,3,0,2,100,'环保新技术股权','[{\"name\":\"环保技术.jpeg\",\"url\":\"\\/public\\/uploads\\/20210916\\/72ec98ec839dceccdd829e58afd7cf1b.jpeg\",\"uid\":\"1631795085552\",\"status\":\"success\"}]','','2022-02-24 09:05:01',999999999.00,10.00,30172000,'本项目募集资金将全部用于昌九高铁项目的建设、维护与开发。股权自购买成功后，第二天开始产生固定收益，股权享受每天持续分红。华夏云投基建项目运作两年，在一年后有筹备上市计划，上市后每天的分红会更高。所有股权一经购买，永久分红。<br /><br />','',2.20,1618666003,2,100,1,999999999,3,'<pre style=\"overflow-wrap: break-word; white-space: pre-wrap;\"><span style=\"font-family: Arial, Helvetica, sans-serif;\"></span></pre><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\"></span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">    我是</span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##party_b##</span><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">我为##party_a##代言</span><br /><br /></p><p><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">尊敬的</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##party_b##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">股东,当前您拥有【</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##title##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">】股份</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##gushu##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">股可以享受拥有股权每日</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##bili##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">%股<br /></span></span></p><p><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">权</span></span><span style=\"white-space: pre-wrap; color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace;\">分红收益。</span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">此股权证书为股东投资入股的合法凭证。    </span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\"><br /></span></p><div style=\"top: 0px;\"><pre style=\"overflow-wrap: break-word; white-space: pre-wrap;\"></pre></div>',1,3,28,4.00,1,89,0),(29,3,0,2,100,'生物新技术股权','[{\"name\":\"生物技术.jpg\",\"url\":\"\\/public\\/uploads\\/20210916\\/6f694c429b7ab212b006f9cbb1762a14.jpg\",\"uid\":\"1631795132783\",\"status\":\"success\"}]','','2022-02-24 09:04:24',999999999.00,30.00,14313000,'本项目募集资金将全部用于嘉兴1号海上风电项目的建设、维护与开发。股权自购买成功后，第二天开始产生固定收益，股权享受每天持续分红。华夏云投基建项目运作两年，在一年后有筹备上市计划，上市后每天的分红会更高。所有股权一经购买，永久分红。<br /><br /><br />','',2.60,1618666003,2,100,1,99999999,3,'<pre style=\"overflow-wrap: break-word; white-space: pre-wrap;\"><span style=\"font-family: Arial, Helvetica, sans-serif;\"></span></pre><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\"></span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">    我是</span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##party_b##</span><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">我为##party_a##代言</span><br /><br /></p><p><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">尊敬的</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##party_b##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">股东,当前您拥有【</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##title##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">】股份</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##gushu##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">股可以享受拥有股权每日</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##bili##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">%股<br /></span></span></p><p><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">权</span></span><span style=\"white-space: pre-wrap; color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace;\">分红收益。</span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">此股权证书为股东投资入股的合法凭证。    </span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\"><br /></span></p><div style=\"top: 0px;\"><pre style=\"overflow-wrap: break-word; white-space: pre-wrap;\"></pre></div>',1,3,29,4.00,1,71,0),(30,2,0,2,100,'张健助学基金','[{\"name\":\"photo_2021-09-20_14-45-49.jpg\",\"url\":\"\\/public\\/uploads\\/20210920\\/f239ab25fc93648b4e46dfe20c7f9050.jpg\",\"uid\":\"1632120366120\",\"status\":\"success\"}]','','2022-02-14 14:45:39',999999999.00,500.00,2403500,'本项目募集资金将全部用于昌九高铁项目的建设、维护与开发。基金自购买成功后，第二天开始产生固定收益，基金持有期间享受每天返利。基金购买成功三个月内不可赎回本金，每天分红，分红可以提现，三个月后可以继续持有基金，继续享受每天返利；或者联系客服申请赎回，基金本金赎回后，返利停止。基金不赎回，享受永久返利！<br /><br /><br /><pre style=\"overflow-wrap: break-word; white-space: pre-wrap;\"></pre>','',2.00,1618664234,2,1,1,889999,2,'',0,1,33,0.00,0,75,0),(31,3,0,2,100,'信息新技术股权','[{\"name\":\"信息技术.jpg\",\"url\":\"\\/public\\/uploads\\/20210916\\/903064f1e1c77594bb08fb06fd9d4057.jpg\",\"uid\":\"1631795110488\",\"status\":\"success\"}]','','2022-02-24 09:05:42',999999999.00,20.00,27444000,'本项目募集资金将全部用于舟岱跨海大桥项目的建设、维护与开发。股权自购买成功后，第二天开始产生固定收益，股权享受每天持续分红。华夏云投基建项目运作两年，在一年后有筹备上市计划，上市后每天的分红会更高。所有股权一经购买，永久分红。<br /><br />','',2.50,1618666003,2,100,1,999999,3,'<pre style=\"overflow-wrap: break-word; white-space: pre-wrap;\"><span style=\"font-family: Arial, Helvetica, sans-serif;\"></span></pre><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\"></span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">    我是</span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##party_b##</span><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">我为##party_a##代言</span><br /><br /></p><p><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">尊敬的</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##party_b##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">股东,当前您拥有【</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##title##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">】股份</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##gushu##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">股可以享受拥有股权每日</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##bili##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">%股<br /></span></span></p><p><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">权</span></span><span style=\"white-space: pre-wrap; color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace;\">分红收益。</span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">此股权证书为股东投资入股的合法凭证。    </span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\"><br /></span></p><div style=\"top: 0px;\"><pre style=\"overflow-wrap: break-word; white-space: pre-wrap;\"></pre></div>',1,3,31,4.00,0,72,0),(32,1,0,2,0,'云计划数字货币5元','[{\"name\":\"5.jpg\",\"url\":\"\\/public\\/uploads\\/20210924\\/55b02f5862148609ecb375e9d6d82041.jpg\",\"uid\":\"1632464963182\",\"status\":\"success\"}]','123','2021-10-03 22:05:11',5.00,3.80,328307,'华夏云投所有货币，会由集团公司按照回收当天的市值统一现金回收！回收周期为两个月一次，第一批的回收日期是10月1日至10月7日！想要卖出货币的会员，只需要在回收日期内联系客服进行回收即可，回收资金当天可到账！货币具有升值功能，货币没有分红。<br /><br /><br /><br />','',0.00,1621596990,2,100,0,999999999,1,'',0,1,32,0.00,0,0,0),(33,1,0,2,0,'云计划数字货币100元','[{\"name\":\"100.jpg\",\"url\":\"\\/public\\/uploads\\/20210924\\/d770f367bdc3d1264c6e7ef464371b92.jpg\",\"uid\":\"1632465011914\",\"status\":\"success\"}]','','2021-10-03 22:04:02',100.00,60.00,88263,'华夏云投所有货币，会由集团公司按照回收当天的市值统一现金回收！回收周期为两个月一次，第一批的回收日期是10月1日至10月7日！想要卖出货币的会员，只需要在回收日期内联系客服进行回收即可，回收资金当天可到账！货币具有升值功能，货币没有分红。<br /><br /><br />','',0.00,1621839702,2,100,0,888888888,1,'',0,1,33,0.00,0,0,0),(34,2,0,2,100,'张健养老基金','[{\"name\":\"cbe561ba0aac4bed92465b5685793ef4 (1).jpeg\",\"url\":\"\\/public\\/uploads\\/20210920\\/cf33fd4dd5b77e5d7bf44394ae7433d4.jpeg\",\"uid\":\"1632120678168\",\"status\":\"success\"}]','','2022-02-17 20:31:11',99999999.00,3000.00,231000,'本项目募集资金将全部用于白鹤滩水电站项目的建设、维护与开发。基金自购买成功后，第二天开始产生固定收益，基金持有期间享受每天返利。基金购买成功三个月内不可赎回本金，每天分红，分红可以提现，三个月后可以继续持有基金，继续享受每天返利；或者联系客服申请赎回，基金本金赎回后，返利停止。基金不赎回，享受永久返利！<br /><br /><br />','',2.60,1623764850,2,1,1,2147483647,2,'',0,1,33,0.00,0,47,0),(36,3,0,2,100,'新材料技术股权','[{\"name\":\"新材料技术.jpg\",\"url\":\"\\/public\\/uploads\\/20210916\\/ed4c9bc933754df785c90704c831ecf5.jpg\",\"uid\":\"1631795152246\",\"status\":\"success\"}]','','2022-02-24 09:04:11',999999999.00,40.00,6368000,'本项目募集资金将全部用于白鹤滩水电站项目的建设、维护与开发。股权自购买成功后，第二天开始产生固定收益，股权享受每天持续分红。华夏云投基建项目运作两年，在一年后有筹备上市计划，上市后每天的分红会更高。所有股权一经购买，永久分红。<br /><br /><br />','',2.70,1623988338,2,100,1,2147483647,3,'<pre style=\"overflow-wrap: break-word; white-space: pre-wrap;\"><span style=\"font-family: Arial, Helvetica, sans-serif;\"></span></pre><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\"></span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">    我是</span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##party_b##</span><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">我为##party_a##代言</span><br /><br /></p><p><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">尊敬的</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##party_b##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">股东,当前您拥有【</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##title##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">】股份</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##gushu##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">股可以享受拥有股权每日</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##bili##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">%股<br /></span></span></p><p><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">权</span></span><span style=\"white-space: pre-wrap; color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace;\">分红收益。</span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">此股权证书为股东投资入股的合法凭证。    </span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\"><br /></span></p><div style=\"top: 0px;\"><pre style=\"overflow-wrap: break-word; white-space: pre-wrap;\"></pre></div>',1,3,36,4.00,0,73,0),(37,3,0,2,100,'新能源技术股权','[{\"name\":\"新能源技术2.jpg\",\"url\":\"\\/public\\/uploads\\/20210916\\/2bbf586c485757335136f714f0093c50.jpg\",\"uid\":\"1631795178586\",\"status\":\"success\"}]','','2022-02-24 09:03:48',99999.00,50.00,43755000,'本项目募集资金将全部用于海南新机场的建设、维护与开发。股权自购买成功后，第二天开始产生固定收益，股权享受每天持续分红。华夏云投基建项目运作两年，在一年后有筹备上市计划，上市后每天的分红会更高。所有股权一经购买，永久分红。<br /><br />','',2.80,1623988456,2,100,1,1000000,3,'<pre style=\"overflow-wrap: break-word; white-space: pre-wrap;\"><span style=\"font-family: Arial, Helvetica, sans-serif;\"></span></pre><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\"></span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">    我是</span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##party_b##</span><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">我为##party_a##代言</span><br /><br /></p><p><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">尊敬的</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##party_b##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">股东,当前您拥有【</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##title##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">】股份</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##gushu##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">股可以享受拥有股权每日</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##bili##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">%股<br /></span></span></p><p><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">权</span></span><span style=\"white-space: pre-wrap; color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace;\">分红收益。</span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">此股权证书为股东投资入股的合法凭证。    </span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\"><br /></span></p><div style=\"top: 0px;\"><pre style=\"overflow-wrap: break-word; white-space: pre-wrap;\"></pre></div>',1,3,37,4.00,0,72,0),(45,22,0,2,7,'人工智能7天封闭期基金','[{\"name\":\"人工智能.jpg\",\"url\":\"\\/public\\/uploads\\/20210707\\/433e61ab7600c6a879f452d363822017.jpg\",\"uid\":\"1625588446273\",\"status\":\"success\"}]','测试','2021-07-14 09:59:43',8.00,1000.00,3000,'','',25.00,1625553437,1,1,1,1,4,'',0,0,0,0.00,0,0,0),(48,22,0,2,15,'人工智能15天封闭期基金','[{\"name\":\"人工智能.jpg\",\"url\":\"\\/public\\/uploads\\/20210707\\/847f91fd5396af8657d5e1a205d2a2ca.jpg\",\"uid\":\"1625588507973\",\"status\":\"success\"}]','112','2021-07-22 10:00:41',99.00,3000.00,8000,'','',80.00,1625586393,1,1,1,5,4,'',0,0,0,0.00,0,0,0),(49,22,0,2,3,'人工智能3天封闭期基金','[{\"name\":\"人工智能.jpg\",\"url\":\"\\/public\\/uploads\\/20210707\\/1d2e5400b835c3aa6c5b8ebd8293e90e.jpg\",\"uid\":\"1625588260596\",\"status\":\"success\"}]','1220','2021-07-10 09:58:54',12.00,600.00,1800,'','',20.00,1625586553,1,1,1,1,4,'',0,0,0,0.00,0,0,0),(54,22,0,2,30,'人工智能30天封闭期基金','[{\"name\":\"人工智能.jpg\",\"url\":\"\\/public\\/uploads\\/20210707\\/804f27cf6d6f1e62548ede3027e5e204.jpg\",\"uid\":\"1625588559170\",\"status\":\"success\"}]','','2021-08-06 10:02:04',999.00,10000.00,200000,'','',200.00,1625588625,1,1,1,5,4,'',0,0,0,0.00,0,0,0),(55,22,0,2,60,'人工智能60天封闭期基金','[{\"name\":\"人工智能.jpg\",\"url\":\"\\/public\\/uploads\\/20210707\\/30118783cf2e85ab01d80ba34647c3c7.jpg\",\"uid\":\"1625588654465\",\"status\":\"success\"}]','','2021-09-05 10:02:34',999.00,25000.00,200000,'','',300.00,1625588718,1,1,1,5,4,'',0,0,0,0.00,0,0,0),(56,22,0,2,20,'人工智能20天封闭期基金','[{\"name\":\"人工智能.jpg\",\"url\":\"\\/public\\/uploads\\/20210707\\/9e90a66ac4f0d87dcaecff04e111224e.jpg\",\"uid\":\"1625622967324\",\"status\":\"success\"}]','','2021-07-27 09:57:32',999.00,5000.00,100000,'','',120.00,1625623052,1,1,1,5,4,'',0,1,0,0.00,0,0,0),(57,1,0,2,0,'云计划数字货币1元','[{\"name\":\"1.jpg\",\"url\":\"\\/public\\/uploads\\/20210924\\/947e418910c2c27bc64e9261d464baf3.jpg\",\"uid\":\"1632464951111\",\"status\":\"success\"}]','5222','2021-10-04 20:28:08',1.00,0.80,88798,'华夏云投所有货币，会由集团公司按照回收当天的市值统一现金回收！回收周期为两个月一次，第一批的回收日期是10月1日至10月7日！想要卖出货币的会员，只需要在回收日期内联系客服进行回收即可，回收资金当天可到账！货币具有升值功能，货币没有分红。<br /><br />','',0.00,1626769497,2,500,0,2147483647,1,'',0,1,0,0.00,0,0,0),(59,1,0,2,0,'云计划数字货币20元','[{\"name\":\"20.jpg\",\"url\":\"\\/public\\/uploads\\/20210924\\/28129be0de76a7fc71424690d1322a43.jpg\",\"uid\":\"1632464988826\",\"status\":\"success\"}]','','2021-10-03 22:04:42',20.00,14.00,29034,'华夏云投所有货币，会由集团公司按照回收当天的市值统一现金回收！回收周期为两个月一次，第一批的回收日期是10月1日至10月7日！想要卖出货币的会员，只需要在回收日期内联系客服进行回收即可，回收资金当天可到账！货币具有升值功能，货币没有分红。<br /><br /><br />','',0.00,1627465134,2,100,0,2147483647,1,'',0,1,0,0.00,0,0,0),(60,1,0,2,0,'云计划数字货币50元','[{\"name\":\"50.jpg\",\"url\":\"\\/public\\/uploads\\/20210924\\/32b83329935cc8faa81ec9ddcab3c96c.jpg\",\"uid\":\"1632465001667\",\"status\":\"success\"}]','','2021-10-03 22:04:27',50.00,30.00,166483,'华夏云投所有货币，会由集团公司按照回收当天的市值统一现金回收！回收周期为两个月一次，第一批的回收日期是10月1日至10月7日！想要卖出货币的会员，只需要在回收日期内联系客服进行回收即可，回收资金当天可到账！货币具有升值功能，货币没有分红。<br /><br />','',0.00,1627465273,1,100,0,2147483647,1,'',0,0,0,0.00,0,0,1642670496),(61,3,0,2,1000,'海洋新技术股权','[{\"name\":\"海洋技术.jpg\",\"url\":\"\\/public\\/uploads\\/20210916\\/81ffb2ccd6952ced015ddd59b19ddf24.jpg\",\"uid\":\"1631795213704\",\"status\":\"success\"}]','5222','2024-08-12 09:03:41',999999999.99,100.00,87630000,'本项目募集资金将全部用于北京上德商业综合体项目的建设、维护与开发。股权自购买成功后，第二天开始产生固定收益，股权享受每天持续分红。华夏云投基建项目运作两年，在一年后有筹备上市计划，上市后每天的分红会更高。所有股权一经购买，永久分红。<br />','',3.00,1628218837,2,100,1,9999,3,'<p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">我是</span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##party_b##</span><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">我为##party_a##代言</span><br /><br /></p><p><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">尊敬的</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##party_b##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">股东,当前您拥有【</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##title##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">】股份</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##gushu##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">股可以享受拥有股权每日</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##bili##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">%股<br /></span></span></p><p><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">权</span></span><span style=\"white-space: pre-wrap; color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace;\">分红收益。</span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">此股权证书为股东投资入股的合法凭证。    </span></p><div><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\"><br /></span></div>',1,3,61,4.00,0,79,0),(62,3,0,2,1000,'空间领域新技术股权','[{\"name\":\"空间技术.jpg\",\"url\":\"\\/public\\/uploads\\/20210916\\/9f0d71bf165d90eaca37af766041de7e.jpg\",\"uid\":\"1631795241302\",\"status\":\"success\"}]','8522','2024-08-12 09:03:26',999999999.99,500.00,47900000,'本项目募集资金将全部用于香港国际机场第三跑道的建设、维护与开发。股权自购买成功后，第二天开始产生固定收益，股权享受每天持续分红。华夏云投基建项目运作两年，在一年后有筹备上市计划，上市后每天的分红会更高。所有股权一经购买，永久分红。<br />','',3.20,1628218917,2,100,1,100000,3,'',1,3,62,4.00,1,64,0),(65,2,0,2,90,'张健医疗改善基金','[{\"name\":\"KTBN3lamPc.jpg\",\"url\":\"\\/public\\/uploads\\/20210920\\/eb1e9450833662b61767ac85844a3f22.jpg\",\"uid\":\"1632120922972\",\"status\":\"success\"}]','','2022-02-07 20:30:58',999999999.99,5000.00,690000,'','',2.80,1632120968,2,1,0,10000,2,'',0,1,33,0.00,0,59,0),(66,2,0,2,90,'张健育儿基金1','/uploads/202202/20220206095652THMqF.jpg','','2022-02-06 00:00:00',9999.00,1201.00,1661122,'','',3.00,1632121510,1,1,0,2147483647,2,'',1,1,33,0.00,1,62,1644112852),(67,2,0,2,90,'张健自然灾害救助基金','/uploads/202202/20220206095755TqVoh.jpeg','','2022-02-06 00:00:00',9999.00,5010.00,350000,'','',3.20,1632122918,1,1,0,10000,2,'',1,1,0,0.00,1,43,1644112684),(68,3,0,2,9999,'生物医学工程新技术股权','[{\"name\":\"aae963551efb4468a228828f4678619d_th.jpeg\",\"url\":\"\\/public\\/uploads\\/20211007\\/ae14b83e94e723338306e687f6fa9f78.jpeg\",\"uid\":\"1633610891389\",\"status\":\"success\"}]','','2049-04-02 09:03:34',999999.00,200.00,64260000,'','',3.10,1633610916,2,100,0,2147483647,3,'<p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">    我是</span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##party_b##</span><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">我为##party_a##代言</span><br /><br /></p><p><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">尊敬的</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##party_b##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">股东,当前您拥有【</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##title##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">】股份</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##gushu##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">股可以享受拥有股权每日</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##bili##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">%股<br /></span></span></p><p><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">权</span></span><span style=\"white-space: pre-wrap; color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace;\">分红收益。</span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">此股权证书为股东投资入股的合法凭证。    </span></p><div><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\"><br /></span></div>',1,3,68,4.00,1,80,0),(69,2,0,2,9999,'张健关爱留守儿童基金','[{','','2022-02-06 00:00:00',99999.00,20010.00,920000,'','',3.10,1633612260,1,1,0,2147483647,2,'',1,1,33,0.00,1,49,1644112183),(70,3,0,2,999,'碳中和新技术股权','[{\"name\":\"unnamed.jpg\",\"url\":\"\\/public\\/uploads\\/20211115\\/906d30765eb4d6763b63a9c3eb0491e2.jpg\",\"uid\":\"1636935961436\",\"status\":\"success\"}]','','2024-08-11 09:04:42',99999.00,25.00,1545000,'','',2.55,1636936036,2,100,1,2147483647,3,'<p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">    我是</span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##party_b##</span><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">我为##party_a##代言</span><br /><br /></p><p><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">尊敬的</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##party_b##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">股东,当前您拥有【</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##title##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">】股份</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##gushu##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">股可以享受拥有股权每日</span></span><span style=\"color: rgb(64, 158, 255); font-family: &quot;Microsoft YaHei&quot;; white-space: nowrap; background-color: rgba(64, 158, 255, 0.1);\">##bili##</span><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">%股<br /></span></span></p><p><span style=\"font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; color: rgb(34, 34, 34);\"><span style=\"white-space: pre-wrap;\">权</span></span><span style=\"white-space: pre-wrap; color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace;\">分红收益。</span></p><p><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\">此股权证书为股东投资入股的合法凭证。    </span></p><div><span style=\"color: rgb(34, 34, 34); font-family: consolas, &quot;lucida console&quot;, &quot;courier new&quot;, monospace; white-space: pre-wrap;\"><br /></span></div>',1,3,70,4.00,0,12,0),(71,0,0,0,0,'asdf','',NULL,'2022-01-20 00:00:00',0.00,0.00,0,'asdf',NULL,0.00,1642666193,1,0,1,0,3,NULL,1,1,0,0.00,1,0,1642666193);
/*!40000 ALTER TABLE `product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rebate_log`
--

DROP TABLE IF EXISTS `rebate_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rebate_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL DEFAULT '0' COMMENT '订单号',
  `money` double(20,2) NOT NULL DEFAULT '0.00' COMMENT '返利金额',
  `create_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rebate_log`
--

LOCK TABLES `rebate_log` WRITE;
/*!40000 ALTER TABLE `rebate_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `rebate_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recharge`
--

DROP TABLE IF EXISTS `recharge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recharge` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `trade_no` varchar(255) NOT NULL DEFAULT '' COMMENT '订单号',
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `money` double(20,2) unsigned NOT NULL COMMENT '充值金额',
  `img` varchar(1000) DEFAULT NULL,
  `type` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '类型，1线下，2线上',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态，0待支付，1已支付，2线下待审核，3线下通过，4线下拒绝',
  `create_time` int unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `is_show` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否显示',
  `bz` varchar(255) NOT NULL COMMENT '备注',
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT '用户名称',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`),
  KEY `recharge_trade_no_idx` (`trade_no`),
  KEY `recharge_type_idx` (`type`),
  KEY `recharge_status_idx` (`status`),
  KEY `recharge_is_show_idx` (`is_show`),
  KEY `recharge_username_idx` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recharge`
--

LOCK TABLES `recharge` WRITE;
/*!40000 ALTER TABLE `recharge` DISABLE KEYS */;
/*!40000 ALTER TABLE `recharge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recharge_log`
--

DROP TABLE IF EXISTS `recharge_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recharge_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `money` double(20,2) NOT NULL DEFAULT '0.00' COMMENT '操作金额',
  `type` int NOT NULL COMMENT '类型【10扣取体验金12扣取可提金额】',
  `create_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_id` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recharge_log`
--

LOCK TABLES `recharge_log` WRITE;
/*!40000 ALTER TABLE `recharge_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `recharge_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '角色名称',
  `description` varchar(255) NOT NULL COMMENT '描述',
  `menu_auth` text NOT NULL COMMENT '菜单权限',
  `status` int NOT NULL DEFAULT '1' COMMENT '状态：1启用2禁用',
  `create_time` int NOT NULL COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role`
--

LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` VALUES (1,'审核员','专门负责审核操作','[\"7-1\",\"7-3\",\"7-4\",\"7-5\",\"8\",\"8-1\",\"8-2\",\"8-3\"]',1,1632931023,0),(3,'资料员','专门录入产品资料的人员','[\"7\",\"7-1\",\"7-2\",\"7-3\",\"7-4\",\"7-5\",\"8\",\"8-1\",\"8-2\",\"8-3\"]',1,1632931015,0),(4,'出入员','出入员','[\"7\",\"7-1\",\"7-2\",\"7-3\",\"7-4\",\"7-5\",\"8\",\"8-1\",\"8-2\",\"8-3\"]',2,1632931008,1642734735);
/*!40000 ALTER TABLE `role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sign`
--

DROP TABLE IF EXISTS `sign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sign` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0',
  `day` int NOT NULL DEFAULT '0' COMMENT '累计签到天数',
  `money` double NOT NULL DEFAULT '0' COMMENT '签到加多少钱',
  `day_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '签到日期',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uid_day_time` (`uid`,`day_time`) USING BTREE,
  KEY `sign_day_time_idx` (`day_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sign`
--

LOCK TABLES `sign` WRITE;
/*!40000 ALTER TABLE `sign` DISABLE KEYS */;
/*!40000 ALTER TABLE `sign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system`
--

DROP TABLE IF EXISTS `system`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system` (
  `id` int NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  `update_time` int NOT NULL DEFAULT '0' COMMENT '最后修改时间',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system`
--

LOCK TABLES `system` WRITE;
/*!40000 ALTER TABLE `system` DISABLE KEYS */;
INSERT INTO `system` VALUES (1,'name','云计划',1643520165,0),(2,'moneys','200,2000,5000',1643520165,0),(3,'one','22',1643520232,0),(4,'two','5',1643520232,0),(5,'template','539907',0,0),(8,'welfare','5000',1643520165,0),(9,'invite','邀请说明',1643520165,0),(10,'reg_gift_money','1',1643520266,0),(11,'protocol','一、云计划用户注册及服务协议\n欢迎使用[云计划] 产品及服务！\n提示：在使用[云计划]产品及服务之前，您应当认真阅读并遵守《云计划服务协议》（以下简称“本协议”）以及《[云计划]隐私政策》。请您务必审慎阅读、充分理解各条款内容，特别是免除或者限制责任的条款、争议解决和法律适用条款。免除或者限制责任的条款可能将以加粗字体显示，您应重点阅读。\n当您按照注册页面提示填写信息、阅读并同意本协议且完成全部注册程序后，或您按照激活页面提示填写信息、阅读并同意本协议且完成全部激活程序后，或您以其他[平台名称]允许的方式实际使用本服务时，即表示您已充分阅读、理解并接受本协议的全部内容，本协议即产生法律约束力。您承诺接受并遵守本协议的约定，届时您不应以未阅读本协议的内容或者未获得[平台名称]对您问询的解答等理由，主张本协议无效或要求撤销本协议。\n一、缔约主体\n本协议由通过[云计划]网站、移动客户端及其他方式使用[云计划]服务的用户（以下简称“用户”或“您”）与【云计划集团】有限公司（以下合称“[云计划]公司”、“[云计划]”或“我们”）共同缔结。\n二、协议内容和效力\n本协议内容包括本协议正文及所有我们已经发布或将来可能发布的隐私政策、各项政策、规则、声明、通知、警示、提示、说明（以下简称“规则”）。前述规则为本协议不可分割的组成部分，与本协议具有同等法律效力。\n三、服务内容\n1、[云计划]产品及服务包括[云计划]官网，[云计划]移动客户端及其他方式使用[云计划]服务的形式。[云计划]公司会不断丰富您使用本服务的终端、形式等，如您已注册使用一种形式的服务，则可以以同一账号使用其他服务，本协议自动适用于您对所有版本的软件和服务的使用。\n2、[云计划]依据本协议、《[云计划]隐私政策》以及其他适用的规则，许可您对[云计划]软件进行不可转让的、非排他性的使用。您可以制作本软件的一个副本，仅用作备份。备份副本必须包含原软件中含有的所有著作权信息，本条及本协议其他条款未明示授权的其他一切权利仍由[平台名称]公司保留，您在行使这些权利时须另外取得[平台名称]公司的书面许可。[云计划]公司如果未行使前述任何权利，并不构成对该权利的放弃。\n3、[云计划]公司有权自行决定对服务或服务任何部分及其相关功能、应用软件进行变更、升级、修改、转移，并在[云计划]官网上公示通知。 \n四、注册及账号管理\n1、您在使用[云计划]的服务时可能需要注册一个帐号。但您没有注册[云计划]的账号，仅有权使用浏览功能，无法正常使用[云计划]提供的服务。\n2、[云计划]特别提醒您应妥善保管您的帐号和密码。当您使用完毕后，应安全退出。因您保管不善可能导致遭受盗号或密码失窃，责任由您自行承担。\n3、您确认，在您完成注册程序或以其他[云计划]公司允许的方式实际使用服务时，您应当是具备完全民事权利能力和与所从事的民事行为相适应的行为能力的自然人、法人或其他组织。若您不具备前述主体资格，请勿使用服务，否则您及您的监护人应承担因此而导致的一切后果，且[云计划]公司有权注销（永久冻结）您的账户，并向您及您的监护人索偿。如您代表一家企业、组织机构或其他法律主体进行注册或以其他[云计划]公司允许的方式实际使用本服务，则您声明和保证，您已经获得充分授权并有权代表该公司、组织机构或法律主体注册使用[平台名称]服务，并受本协议地约束。\n4、您可以使用您提供或确认的手机号或者[云计划]允许的其它方式作为账号进行注册, 您注册时应提交真实、准确、完整和反映当前情况的身份及其他相关信息。你理解并同意，您承诺注册的账号名称、头像信息中不得出现违法和不良信息，不得冒充他人，不得未经许可为他人注册，不得以可能导致其他用户误认的方式注册账号，不得使用可能侵犯他人权益的用户名（包括但不限于涉嫌商标权、名誉权侵权等），否则[云计划]公司有权不予注册或停止服务并收回账号，因此产生的损失由您自行承担。\n5、您了解并同意，[云计划]注册账号所有权归属于云计划]公司，注册完成后，您仅获得账号使用权。\n五、服务使用规范\n1、您承诺不会利用本服务进行任何违法或不当的活动，包括但不限于下列行为:\n2.1 上载、传送或分享含有下列内容之一的信息：(a) 反对宪法所确定的基本原则的；(b) 危害国家安全，泄露国家秘密，颠覆国家政权，破坏国家统一的；(c) 损害国家荣誉和利益的；(d) 煽动民族仇恨、民族歧视、破坏民族团结的；(e) 破坏国家宗教政策，宣扬邪教和封建迷信的；(f) 散布谣言，扰乱社会秩序，破坏社会稳定的；(g) 散布淫秽、色情、赌博、暴力、凶杀、恐怖或者教唆犯罪的；(h) 侮辱或者诽谤他人，侵害他人合法权利的；(i) 含有虚假、诈骗、有害、胁迫、侵害他人隐私、骚扰、侵害、中伤、粗俗、猥亵、或其它道德上令人反感的内容；(j) 含有中国或其您所在国国家管辖法所适用的法律、法规、规章、条例以及任何具有法律效力之规范所限制或禁止的其它内容的；\n1.2 冒充任何人或机构，或以虚伪不实的方式陈述或谎称与任何人或机构有关；\n1.3 伪造标题或以其他方式操控识别资料，使人误认为该内容为[云计划]公司或其关联公司所传送；\n1.4 将依据任何法律或合约或法定关系（例如由于雇佣关系和依据保密合约所得知或揭露之内部资料、专属及机密资料）知悉但无权传送之任何内容加以上载、传送或分享；\n1.5 将涉嫌侵害他人权利（包括但不限于著作权、专利权、商标权、商业秘密等知识产权）之内容上载、传送或分享；\n1.6 违反遵守法律法规、社会主义制度、国家利益、公民合法利益、公共秩序、社会道德风尚和信息真实性等“七条底线”要求的行为；\n1.7从事任何违反中国法律、法规、规章、政策及规范性文件的行为。\n2、您承诺不对本软件和服务从事以下行为：\n2.1 将有关干扰、破坏或限制任何计算机软件、硬件或通讯设备功能的软件病毒或其他计算机代码、档案和程序之资料，加以上载或以其他方式传送；\n2.2 干扰或破坏本服务或与本服务相连线之服务器和网络，或违反任何关于本服务连线网络之规定、程序、政策或规范；\n2.3 通过修改或伪造软件运行中的指令、数据，增加、删减、变动软件的功能或运行效果，或者将用于上述用途的软件、方法进行运营或向公众传播，无论这些行为是否为商业目的；\n2.4 通过非[云计划]公司开发、授权的第三方软件、插件、外挂、系统，登录或使用软件及服务，或制作、发布、传播上述工具；\n2.5 自行、授权他人或利用第三方软件对本软件及其组件、模块、数据等进行干扰。\n3、您承诺，使用[云计划]服务时您将严格遵守本协议。\n4、您同意并接受[云计划]公司有权对您使用服务的情况进行审查、监督并采取相应行动，包括但不限于删除信息、中止或终止服务，及向有关机关报告。\n5、您承诺不以任何形式使用本服务侵犯[云计划]公司的商业利益，或从事任何可能对[平台名称]造成损害或不利于[云计划]的行为。\n13、如果[云计划]公司发现或收到他人举报您有违反本协议约定的，[云计划]公司有权依照相关法律法规的规定对相关举报内容核实、转通知以及删除、屏蔽等措施，以及采取包括但不限于收回账号，限制、暂停、终止您使用部分或全部本服务，追究法律责任等措施。\n六、服务费用\n1、本服务的任何免费试用或免费功能和服务不应视为[云计划]公司放弃后续收费的权利。若您继续使用相关[云计划]服务，则需按[云计划]公司公布的收费标准支付相应费用。\n2、您购买的[云计划]服务所有费用需通过[云计划]接受的支付方式事先支付。前述使用费不包含其它任何税款、费用或相关汇款等支出，否则您应补足付款或自行支付该费用。\n3、您应当自行支付使用本服务可能产生的上网费以及其他第三方收取的通讯费、信息费等。\n七、第三方应用及服务\n1、企业自建应用：对于您在[云计划]平台使用企业内配置对接的第三方应用服务时，[云计划]将在您启动该应用时收集相关信息，并按照本隐私协议进行处理。第三方应用服务提供者可能会获取您在[平台名称]的个人信息，该服务对信息的收集受其自身隐私协议的约束。\n2、您了解并同意，除法律另有明确规定外，如我们对云计划]服务及第三方服务做出调整、中止或终止而对第三方应用服务产生影响的，[云计划]公司不承担相应责任。\n3、您通过第三方应用或服务使用[云计划]时，[云计划]可能会调用第三方系统或者通过第三方支持您的使用或访问，使用或访问的结果由该第三方提供。\n4、您理解并同意，您在使用[云计划]服务中的第三方应用及服务时，除遵守本协议的约定外，还应遵守第三方用户协议；为实现第三方应用及服务，企业服务管理员基于企业组织授权择开通第三方服务，向该独立第三方提供企业通讯录信息。您在选择使用第三服务前应充分了解第三方服务的产品功能、服务协议及隐私保护政策，再选择是否开通功能并遵守第三方用户协议。\n八、隐私政策\n1、您在[云计划]服务注册的账户具有密码保护功能，以确保您基本信息资料的安全，请您妥善保管账户及密码信息。\n2、[云计划]公司努力采取各种合理的物理、电子和管理方面的安全措施来保护您的信息，使您存储在[云计划]中的信息和通信内容不会被泄漏、毁损或者丢失，包括但不限于信息加密存储、数据中心的访问控制。我们对可能接触到信息的员工或外包人员也采取了严格管理，包括但不限于根据岗位的不同采取不同的权限控制，与他们签署保密协议，监控他们的操作情况等措施。[云计划]会按现有技术提供相应的安全措施来保护您的信息，提供合理的安全保障，[云计划]将在任何时候尽力做到使您的信息不被泄漏、毁损或丢失，但同时也请您注意在信息网络上不存在绝对完善的安全措施，请妥善保管好相关信息。\n3、您应当保管好终端、账号及密码，并妥善保管相关信息和内容。因您自身原因导致的数据丢失或被盗以及在本软件及服务中相关数据的删除或储存失败的责任由您自行承担。\n4、其他隐私条款见《[云计划]隐私政策》。\n九、知识产权\n1、您了解及同意，除非[云计划]公司另行声明，本协议项下服务包含的所有产品、技术、软件、程序、数据及其他信息（包括但不限于文字、图像、图片、照片、音频、视频、图表、色彩、版面设计、电子文档）的所有知识产权（包括但不限于版权、商标权、专利权、商业秘密等）及相关权利均归[云计划]公司或其关联公司所有。\n2、您应保证，除非取得[云计划]公司书面授权，对于上述权利您不得（并不得允许任何第三人）实施包括但不限于出租、出借、出售、散布、复制、修改、转载、汇编、发表、出版、还原工程、反向汇编、反向编译，或以其它方式发现原始码等的行为。\n3、[云计划]的Logo、“[文字]”商标、图形“[图形]”商标等文字、图形及其组合，以及[云计划]其他标识、徽记、[云计划]服务的名称等为[云计划]公司及其关联公司在中国和其他国家的注册商标。未经[云计划]公司书面授权，任何人不得以任何方式展示、使用或做其他处理（包括但不限于复制、传播、展示、镜像、上传、下载），也不得向他人表明您有权展示、使用或做其他处理。\n4、[云计划]所有的产品、服务、技术与所有应用程序或其组件/功能/名称（以下或简称“技术服务”）的知识产权均归属于[云计划]公司所有或归其权利人所有。\n5、您理解并同意授权[云计划]公司在宣传和推广中使用您的名称及公开渠道可以查询到的企业基本信息，但仅限于表明您属于我们的客户或合作伙伴。\n十、有限责任\n1、服务将按“现状”和按“可得到”的状态提供。[云计划]公司在此明确声明对服务不作任何明示或暗示的保证，包括但不限于对服务的可适用性，没有错误或疏漏，持续性，准确性，可靠性，适用于某一特定用途之类的保证，声明或承诺。\n2、[云计划]对服务所涉的技术和信息的有效性，准确性，正确性，可靠性，质量，稳定，完整和及时性均不作承诺和保证。\n3、不论在何种情况下，[云计划]均不对由于Internet连接故障，电脑，通讯或其他系统的故障，电力故障，罢工，劳动争议，暴乱，起义，骚乱，生产力或生产资料不足，火灾，洪水，风暴，爆炸，不可抗力，战争，政府行为，国际、国内法院的命令或第三方的不作为而造成的不能服务或延迟服务承担责任。\n',1643520165,0),(12,'party_a','云计划',1643520165,0),(13,'reg_onoff','1',1643520266,0),(14,'bank_name','招商银行',1643520229,0),(15,'bank_card','6214832346363778',1643520229,0),(16,'bank_username','周洋',1643520229,0),(17,'sms_uid','yunjia06060606',1643520244,0),(18,'sms_pwd','43bc1bdba0fff75fac7200fe720e76df',1643520244,0),(19,'sms_template','552806',1643520244,0),(20,'sms_onoff','1',1643520244,0),(23,'three','3',1643520232,0),(24,'alipay_appid','2021001195659011',1643520262,0),(25,'alipay_private_key','MIIEowIBAAKCAQEArAf6OiqSScykKWQpzImfRL9yJB5HbmaveRvshqXFbpDxFqYXA0Jun/O1se9PvDk/XR9vVZ7oRUcLznxGfJJJgwkZMrfyDnmixCvsBCPJ2U0mo028jyQGsvKsqpIc26i/3prn8+t3VB5D0Z4t3s0NaHPY5vI1dpREKXU1ODQwqjJdtbyAlh03wE5+sz/+CuCG5Y9Z98NTD2CxNykpVadxuc6ZWFBxtpzGOHzFrhVGNbmnyCIgaU8+y2nKvNbVLO8+w4WY9sM0Nl9918B9ZIMXqJnmT3K9pleHt/iBf/5vs+E6rLn5VjAckOqwF2FRQ5EzM6i3fF3fkP+AbyfJmvYXbwIDAQABAoIBAAiPWQ8d4SuU6DI7Dq1jx64HDKnpa8p9h4uyLQHCK+HFiomC+DAsVQ7WdJsG+mO2QYXjs4mCH7Kax2ad3nACY3AGut9AMeYwfT3fpZ3LHhcW9K45hwFkXIT4+EiAfrkbW3i7IoGLnONtohhDE6s7bshcw1UyhiCBXibl8yD5z9YYGJgFNmuYbpKoIU0byQENBEZsI5YLYbeRYbEYUFLG/+OHKhNXNWV1V5f0StDnF80DGz6MpsEJpXZmaMnvD9VgROzYnb2lJ/UQh4I+suxf85V7Zs1HO78n9+t3sqU5y766xQ9C24MVkTuWI8D+dADMj+J4wBsKFBThQNIjVsAWKvECgYEA1kGn3lQ6q6/5tXDmIS/fjI4JO44LUlxzq+3ASvMSfAW3pVo+vMMfgPqOqZ6dBIE6OGujEY8X6N+RbtA3gE1VxcPQCBeUMPxZmY+U4QgJUqVE8W7MKrd8gM3pBX6qeJ+EdNdSqGEkROvLx/f1M6EB9LzhDm/EnJ5wnd2VtzfRYbcCgYEAzYxGXtaA9DsjkViPjN+OLh6no555xKXO2tuBF+vH2zTUzkVoFNSAP+FiJ+YvHVmE/RZShc9F0cvTZgY57FnSSRpHiVChHAQ1u97NZLJKMx6S36DTFWAUfFJDwkWXZufjlHPUiE6It3JMud+pRS5UeD1sKI4xjIIxfUSz/n4TmAkCgYBVE1b74kg1ujeWQ1Et5luG7TNfUYTPXGSV5V8Qo7o4QvDsGIyG2Zfd2B/j3FaO3l1m396tbztYPcX60XToXkzrrVgijnJKjTlqz2eXf2BsT7GziIJLh/ZJEvovM1+va+/lkUaCE6iaMWMGcUANEya6rnFTETBp3EPte3oieWiLuwKBgQCAcL8oU/GQq8O19xj8dlVqDE3EuCpu7w0rRS6NvA/Oslyn6Eb5fTAGuteTBws5085+N4ypgLOoPS2D+zAGuLnmZD1/WJrT3u2Uz8yYt7AV8WzlwKEmIjuq09zzcHkDHLPg9+A+rJRRzWKUY2ZqjGDMIdWMIg5kNpamKGAxDqc/wQKBgBQI7+h+7ip9khcYO++hkpzDKDwK18rDTYgjTybRZf15YoZkI4Wh/gA0g40GQYl6KfWMP0wEtRo0GY4p09kwyz7lI5tmx2vDc96+J/Y0HBFTLkS2YMnajQoZAs6aGbif1DE7GbQ/AYGboIJgM4Q7QmCihnH/3PLjJF74382ztnve',1643520262,0),(26,'alipay_public_key','MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxMyWYHGzSXS4FWhbmvRfNef+xT4VdosKJ/4U/8+L369uBpJ1typPtSU2G4QmQhUfdpBxCJizMFffH9oTntlDuSvMT1Lsvys1Z7jqCsy1a7T0qrU+xf8GCzxAYyUiphzrD9yNDlQrAhrSQiZEC/bakSqVD+6XqNvsh/Vl7Us2sFQ/FijXoJRLzG8C0d3Qr5hUerlQoZousAmrx3YanosKv5qfq6CC3P5kNAyWQpuTavZXiV8BheJN/LXpoB1E6PUVJ30nyN0o4yoQVprKye5u54FvRFIBGpKTCByigTEexar17FIAzCmsb98tIuciA6Rh3wLFH2djGnUXMS9+baeR6wIDAQAB',1643520262,0),(27,'logo','/uploads/202201/20220129135730TIJQb.jpg',1643520225,0),(28,'withdraw_start_time','09:03:08',1643520165,0),(29,'withdraw_end_time','21:00:00',1643520165,0),(30,'min_withdraw_money','500',1643520165,0),(35,'rebate','0',1643520232,0),(36,'rebate_onoff','2',1643520232,0),(37,'company_images','/uploads/202201/20220125211536TcwD3.jpeg',1643520225,0),(38,'company_desc','华为创立于1987年，是全球领先的ICT（信息与通信）基础设施和智能终端提供商。目前华为约有19.7万员工，业务遍及170多个国家和地区，服务全球30多亿人口。\n               ',1643520165,0),(39,'reward','科学技术奖励制度是我国科技政策的重要组成部分，是党 “尊重劳动、尊重知识、尊重人才、尊重创造”方针的具体体现。',1643520165,0),(40,'give_money_onoff','2',1643520266,0),(43,'give_num1','56',1643520269,0),(44,'give_num2','56',1643520269,0),(45,'give_num3','10',1643520269,0),(46,'give_onoff1','1',1643520269,0),(47,'give_onoff2','1',1643520269,0),(48,'give_onoff3','1',1643520269,0),(49,'give_type1','2',1643520269,0),(50,'give_type2','1',1643520269,0),(51,'give_type3','3',1643520269,0),(52,'give_product_id1','65',1643520269,0),(53,'give_product_id2','59',1643520269,0),(54,'give_product_id3','61',1643520269,0),(55,'sign_give_product_id','33',1643520272,0),(56,'sign_day_num','35',1643520272,0),(57,'sign_product_num','10',1643520272,0),(58,'sign_product_onoff','1',1643520272,0),(59,'privacy','您在[云计划]服务注册的账户具有密码保护功能，以确保您基本信息资料的安全，请您妥善保管账户及密码信息。\n2、[云计划]公司努力采取各种合理的物理、电子和管理方面的安全措施来保护您的信息，使您存储在[云计划]中的信息和通信内容不会被泄漏、毁损或者丢失，包括但不限于信息加密存储、数据中心的访问控制。我们对可能接触到信息的员工或外包人员也采取了严格管理，包括但不限于根据岗位的不同采取不同的权限控制，与他们签署保密协议，监控他们的操作情况等措施。[云计划]会按现有技术提供相应的安全措施来保护您的信息，提供合理的安全保障，[云计划]将在任何时候尽力做到使您的信息不被泄漏、毁损或丢失，但同时也请您注意在信息网络上不存在绝对完善的安全措施，请妥善保管好相关信息。\n3、您应当保管好终端、账号及密码，并妥善保管相关信息和内容。因您自身原因导致的数据丢失或被盗以及在本软件及服务中相关数据的删除或储存失败的责任由您自行承担。\n4、其他隐私条款见《[云计划]隐私政策》。\n',1643520165,0),(60,'stock','1',1643520266,0),(61,'shop_stock_power','1',0,0),(62,'give_stock_onoff','2',1643520266,0),(63,'shop_stock_onoff','1',0,0),(64,'shop_stock_power_jijin','0',1643520165,0),(65,'shop_stock_power_guquan','0',1643520165,0),(66,'h5_domain','http://wap.yjh2466.com',1643520165,0),(67,'down_domain','https://down.yjh9991.com',1643520165,0),(68,'task_status','1',0,0);
/*!40000 ALTER TABLE `system` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test`
--

DROP TABLE IF EXISTS `test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `test` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `money` decimal(10,2) NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `to_uid` int NOT NULL,
  `uid` int NOT NULL,
  `create_time` varchar(50) NOT NULL,
  `type` tinyint NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test`
--

LOCK TABLES `test` WRITE;
/*!40000 ALTER TABLE `test` DISABLE KEYS */;
/*!40000 ALTER TABLE `test` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_order`
--

DROP TABLE IF EXISTS `test_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `test_order` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id编号',
  `product_id` int NOT NULL DEFAULT '0' COMMENT '产品ID编号',
  `trade_no` varchar(255) NOT NULL DEFAULT '' COMMENT '订单编号',
  `img` varchar(255) NOT NULL COMMENT '转账凭证',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单状态，0正在返利，1返利结束,2审核中，3未传凭证,4拒绝',
  `money` double(20,2) NOT NULL DEFAULT '0.00' COMMENT '订单金额',
  `message` varchar(255) DEFAULT NULL COMMENT '备注',
  `create_time` datetime DEFAULT NULL COMMENT '下单时间',
  `end_time` int DEFAULT '0' COMMENT '产品结束返利的时间戳',
  `fl_time` int NOT NULL DEFAULT '0' COMMENT '上次返利的时间',
  `pay_type` int NOT NULL COMMENT '支付类型1余额2支付宝3银行',
  `get_method` int DEFAULT '1' COMMENT '获取方式，1购买2后台添加3购买商品赠送',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_order`
--

LOCK TABLES `test_order` WRITE;
/*!40000 ALTER TABLE `test_order` DISABLE KEYS */;
/*!40000 ALTER TABLE `test_order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '兼容旧版用户编号',
  `account` double(20,2) NOT NULL DEFAULT '0.00' COMMENT '总资产',
  `income` double(20,2) NOT NULL DEFAULT '0.00' COMMENT '累计收益',
  `order_num` int NOT NULL DEFAULT '0' COMMENT '订单数量',
  `username` varchar(255) NOT NULL DEFAULT '' COMMENT '用户名',
  `invite` varchar(255) DEFAULT '' COMMENT '邀请码',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '用户密码',
  `login_ip` varchar(255) NOT NULL DEFAULT '' COMMENT '登陆IP',
  `reg_time` datetime DEFAULT NULL COMMENT '注册时间',
  `login_time` datetime DEFAULT NULL COMMENT '登陆时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1正常用户，2封号',
  `pay_password` varchar(255) NOT NULL DEFAULT '' COMMENT '支付密码',
  `wal_num` int NOT NULL DEFAULT '0',
  `level` tinyint(1) NOT NULL DEFAULT '1' COMMENT '会员等级',
  `ex_gold` double(20,2) NOT NULL DEFAULT '0.00' COMMENT '理财体验金，后台送',
  `withdraw` int NOT NULL DEFAULT '0',
  `commission` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '总佣金',
  `number` int NOT NULL DEFAULT '0' COMMENT 'Number',
  `vip` int NOT NULL DEFAULT '0' COMMENT 'VIP等级',
  `pid` int NOT NULL DEFAULT '0' COMMENT '上级id',
  `token` varchar(50) NOT NULL DEFAULT '' COMMENT 'Token',
  `stock` varchar(50) DEFAULT '0.00' COMMENT '原始股权',
  `remark` varchar(256) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `user_username_idx` (`username`),
  UNIQUE KEY `user_token_idx` (`token`),
  KEY `invite` (`invite`) USING BTREE,
  KEY `i_user_pid` (`pid`),
  KEY `user_vip_idx` (`vip`),
  KEY `user_login_time_idx` (`login_time`),
  KEY `user_status_idx` (`status`),
  KEY `user_level_idx` (`level`),
  KEY `user_uid_idx` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=168016 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (168001,0,0.00,0.00,0,'13911112222','111111','qwe123','127.0.0.1',NULL,'2022-01-18 22:29:10',1,'qwe123',0,1,1.00,0,0.00,0,0,0,'MFthL2vTvKqHQnk1sdt422x3ryyvDu1F','0.00','sadf',1642045996,1642516150),(168003,0,0.00,0.00,0,'13911119999','8418046','$2y$10$jhGrt0fyvRurLy8vL9vc9eP1ONpiMk1BNLEEG7skQEEmBeWWu95ES','127.0.0.1','2022-01-17 18:57:00','2022-01-17 18:57:00',1,'$2y$10$BODGKuzXgkOcsrXIPZWXnurjg8hNM1sdRcRpSDbixZMxPKd6vV/eS',0,1,0.00,0,0.00,0,0,1,'','0','',1642417020,1642417020),(168004,0,0.00,0.00,0,'13911118888','7164372','$2y$10$ix1BpQfcHXkdkG7t6spq7u15TOudwqC.CKg9JrMH5PEDS3Q8WV7YG','127.0.0.1','2022-01-17 18:58:22','2022-01-17 18:58:22',2,'$2y$10$qF3WsW2tNOQpttRia7Uda.V7eoTe.bedCbWVW2EptpxZ0Qwi1w1FS',0,1,0.00,0,0.00,0,0,1,'FpYTcOGChq9Oo23a0ofo0P6kd4zV2I2C','0','',1642417102,1642734482),(168005,0,0.00,0.00,0,'13911118887','6239923','$2y$10$CJzo9odIzIATy3DIQO9aa.0bMFgWsPUmzMb7ddZ6RnMANaOEp6t2i','127.0.0.1','2022-01-17 18:58:28','2022-01-17 18:58:28',1,'$2y$10$vERW/QKRlSXME86zIIC9FukTFHrbS2nt8haAsUkXQVVi3yh5Fg8e2',0,1,0.00,0,0.00,0,0,1,'zu2cW0MMP6gke0MpIEMwES6CrQt6u1Dh','0','',1642417108,1642417108),(168006,0,0.00,0.00,0,'13911118886','0426676','$2y$10$CtexWDja59Djj3rg1vZb7.1FTiPOOvankldqBZURLbDlPsAdfPb.u','127.0.0.1','2022-01-17 18:58:33','2022-01-17 18:58:33',2,'$2y$10$iSgWWEq71V0YssGN8n3tLeCimV/5DCOAgKZ88j3M92baaPoucKdBS',0,1,0.00,0,0.00,0,0,1,'DyCftIxJ2PqkGU7y9xpthtIRVS8dfAmX','0','',1642417113,1642734469),(168010,0,0.00,0.00,0,'13911118885','4289905','$2y$10$vLOUwZzMB7cb5MCBJEPXue300RjitumIxrBDwd2D6PzXGfGUv6DEi','127.0.0.1','2022-01-17 19:25:32','2022-01-17 19:25:32',2,'$2y$10$iLyP1J../hFer0cQH/9TTeVesrmQ4S74hxD5zluE0lARlSWdYeKRa',0,1,0.00,0,0.00,0,0,1,'JrxgbrLWltIEA9qRmjczAhMEDgdp2QvN','0','',1642418732,1642734477),(168014,0,0.00,0.00,0,'asdfasdf','','qwe123','',NULL,NULL,1,'qwe123',0,1,0.00,0,0.00,0,0,0,'1KP8Xej9e9qlBI7gtIqm3fRbDxJvB9Bl','0.00','',1643455272,1643455272),(168015,0,0.00,0.00,0,'asdfasdf1','3475305','$2y$10$5v4Z6locIaZ1hgYe42rjte4EP.9Xf3.KUUEo4eOh2v4AJbM89wU2i','',NULL,NULL,1,'$2y$10$LmsDN5/7ammGetEqYDPFnejIa4XjnzevoLodHiVRQ2eOdNjCt1npW',0,1,0.00,0,0.00,0,0,0,'Ov6CXfjd11HjtsdHu2biQsAx43xdaZ6j','0.00','管理员后台添加用户',1643455746,1643460114);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_alipay`
--

DROP TABLE IF EXISTS `user_alipay`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_alipay` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uid` int DEFAULT NULL COMMENT '用户id',
  `name` varchar(150) DEFAULT NULL COMMENT '姓名',
  `alipay_account` varchar(150) DEFAULT NULL COMMENT '支付宝账号',
  `create_time` int NOT NULL,
  `update_time` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_alipay`
--

LOCK TABLES `user_alipay` WRITE;
/*!40000 ALTER TABLE `user_alipay` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_alipay` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_bank`
--

DROP TABLE IF EXISTS `user_bank`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_bank` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '户籍所在地',
  `bank_name` varchar(255) NOT NULL DEFAULT '' COMMENT '银行名称',
  `bank_num` varchar(255) NOT NULL DEFAULT '' COMMENT '银行卡号',
  `bank_open` varchar(255) NOT NULL DEFAULT '' COMMENT '开户行',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0待审核，1审核通过，2拒绝',
  `is_default` int NOT NULL DEFAULT '0' COMMENT '默认地址，1默认',
  `create_time` int DEFAULT NULL COMMENT '创建时间',
  `update_time` int DEFAULT NULL COMMENT '更新时间',
  `user_name` varchar(32) NOT NULL DEFAULT '' COMMENT '用户名称',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `i_user_bank_uid` (`uid`),
  KEY `i_user_bank_is_default` (`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_bank`
--

LOCK TABLES `user_bank` WRITE;
/*!40000 ALTER TABLE `user_bank` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_bank` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_ex`
--

DROP TABLE IF EXISTS `user_ex`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_ex` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `idcard` varchar(20) NOT NULL DEFAULT '' COMMENT '身份证号',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '户籍所在地',
  `hongbao` varchar(255) NOT NULL DEFAULT '' COMMENT '红包领取情况',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0待审核，1审核通过，2拒绝',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '修改时间',
  `user_name` varchar(32) NOT NULL DEFAULT '' COMMENT '用户名称',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `i_user_ex_uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_ex`
--

LOCK TABLES `user_ex` WRITE;
/*!40000 ALTER TABLE `user_ex` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_ex` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_invite`
--

DROP TABLE IF EXISTS `user_invite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_invite` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0',
  `pid` int NOT NULL DEFAULT '0',
  `levels` int DEFAULT '0' COMMENT '层级',
  `create_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `i_user_invite_uid` (`uid`),
  KEY `i_user_invite_pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_invite`
--

LOCK TABLES `user_invite` WRITE;
/*!40000 ALTER TABLE `user_invite` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_invite` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_log`
--

DROP TABLE IF EXISTS `user_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '用户编号',
  `username` varchar(20) NOT NULL DEFAULT '' COMMENT '用户名称',
  `object_id` int unsigned NOT NULL DEFAULT '0' COMMENT '对象编号',
  `object_name` varchar(20) NOT NULL DEFAULT '' COMMENT '对象名称',
  `level` tinyint NOT NULL DEFAULT '0' COMMENT '日志等级',
  `type` tinyint NOT NULL DEFAULT '0' COMMENT '日志类型',
  `url` varchar(200) NOT NULL DEFAULT '' COMMENT '调用地址',
  `ip` varchar(64) NOT NULL DEFAULT '' COMMENT '登录IP',
  `user_agent` varchar(256) NOT NULL DEFAULT '' COMMENT '浏览信息',
  `created` int unsigned NOT NULL DEFAULT '0' COMMENT '登录时间',
  `remark` varchar(256) NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`,`created`)
) ENGINE=InnoDB AUTO_INCREMENT=1000003 DEFAULT CHARSET=utf8mb3
/*!50100 PARTITION BY RANGE (`created`)
(PARTITION p2201 VALUES LESS THAN (1643644800) ENGINE = InnoDB,
 PARTITION p2202 VALUES LESS THAN (1646064000) ENGINE = InnoDB,
 PARTITION p2203 VALUES LESS THAN (1648742400) ENGINE = InnoDB,
 PARTITION p2204 VALUES LESS THAN (1651334400) ENGINE = InnoDB,
 PARTITION p2205 VALUES LESS THAN (1654012800) ENGINE = InnoDB,
 PARTITION p2206 VALUES LESS THAN (1656604800) ENGINE = InnoDB,
 PARTITION p2207 VALUES LESS THAN (1659283200) ENGINE = InnoDB,
 PARTITION p2208 VALUES LESS THAN (1661961600) ENGINE = InnoDB,
 PARTITION p2209 VALUES LESS THAN (1664553600) ENGINE = InnoDB,
 PARTITION p2210 VALUES LESS THAN (1667232000) ENGINE = InnoDB,
 PARTITION p2211 VALUES LESS THAN (1669824000) ENGINE = InnoDB,
 PARTITION p2212 VALUES LESS THAN (1672502400) ENGINE = InnoDB,
 PARTITION p2301 VALUES LESS THAN (1675180800) ENGINE = InnoDB,
 PARTITION p2302 VALUES LESS THAN (1677600000) ENGINE = InnoDB,
 PARTITION p2303 VALUES LESS THAN (1680278400) ENGINE = InnoDB,
 PARTITION p2304 VALUES LESS THAN (1682870400) ENGINE = InnoDB,
 PARTITION p2305 VALUES LESS THAN (1685548800) ENGINE = InnoDB,
 PARTITION p2306 VALUES LESS THAN MAXVALUE ENGINE = InnoDB) */;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_log`
--

LOCK TABLES `user_log` WRITE;
/*!40000 ALTER TABLE `user_log` DISABLE KEYS */;
INSERT INTO `user_log` VALUES (1000000,1,'13911112222',1,'13911112222',1,0,'http://ock.local/api/v1/login/login','','',0,'用户登录系统'),(1000001,1,'13911112222',1,'13911112222',1,0,'http://ock.local/api/v1/login/login','','',0,'用户登录系统'),(1000002,1,'13911112222',1,'13911112222',1,0,'http://ock.local/api/v1/login/login','127.0.0.1','',1642516150,'用户登录系统');
/*!40000 ALTER TABLE `user_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_login_log`
--

DROP TABLE IF EXISTS `user_login_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_login_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '用户编号',
  `username` varchar(20) NOT NULL DEFAULT '' COMMENT '用户名称',
  `url` varchar(200) NOT NULL DEFAULT '' COMMENT '调用地址',
  `login_ip` varchar(64) NOT NULL DEFAULT '' COMMENT '登录IP',
  `error_count` int NOT NULL DEFAULT '0' COMMENT '出错次数',
  `user_agent` varchar(256) NOT NULL DEFAULT '' COMMENT '浏览信息',
  `created` int unsigned NOT NULL DEFAULT '0' COMMENT '登录时间',
  `remark` varchar(20) NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`,`created`)
) ENGINE=InnoDB AUTO_INCREMENT=1000012 DEFAULT CHARSET=utf8mb3
/*!50100 PARTITION BY RANGE (`created`)
(PARTITION p2201 VALUES LESS THAN (1643644800) ENGINE = InnoDB,
 PARTITION p2202 VALUES LESS THAN (1646064000) ENGINE = InnoDB,
 PARTITION p2203 VALUES LESS THAN (1648742400) ENGINE = InnoDB,
 PARTITION p2204 VALUES LESS THAN (1651334400) ENGINE = InnoDB,
 PARTITION p2205 VALUES LESS THAN (1654012800) ENGINE = InnoDB,
 PARTITION p2206 VALUES LESS THAN (1656604800) ENGINE = InnoDB,
 PARTITION p2207 VALUES LESS THAN (1659283200) ENGINE = InnoDB,
 PARTITION p2208 VALUES LESS THAN (1661961600) ENGINE = InnoDB,
 PARTITION p2209 VALUES LESS THAN (1664553600) ENGINE = InnoDB,
 PARTITION p2210 VALUES LESS THAN (1667232000) ENGINE = InnoDB,
 PARTITION p2211 VALUES LESS THAN (1669824000) ENGINE = InnoDB,
 PARTITION p2212 VALUES LESS THAN (1672502400) ENGINE = InnoDB,
 PARTITION p2301 VALUES LESS THAN (1675180800) ENGINE = InnoDB,
 PARTITION p2302 VALUES LESS THAN (1677600000) ENGINE = InnoDB,
 PARTITION p2303 VALUES LESS THAN (1680278400) ENGINE = InnoDB,
 PARTITION p2304 VALUES LESS THAN (1682870400) ENGINE = InnoDB,
 PARTITION p2305 VALUES LESS THAN (1685548800) ENGINE = InnoDB,
 PARTITION p2306 VALUES LESS THAN MAXVALUE ENGINE = InnoDB) */;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_login_log`
--

LOCK TABLES `user_login_log` WRITE;
/*!40000 ALTER TABLE `user_login_log` DISABLE KEYS */;
INSERT INTO `user_login_log` VALUES (1000000,1,'13911112222','/api/v1/login/login','127.0.0.1',0,'P',1642160985,'用户登录系统'),(1000001,1,'13911112222','/api/v1/login/login','127.0.0.1',0,'PostmanRuntime/7.28.4',1642161033,'用户登录系统'),(1000002,1,'13911112222','/api/v1/login/login','127.0.0.1',0,'PostmanRuntime/7.28.4',1642403958,'用户登录系统'),(1000003,1,'13911112222','/api/v1/login/login','127.0.0.1',0,'PostmanRuntime/7.28.4',1642422572,'用户登录系统'),(1000004,1,'13911112222','/api/v1/login/login','127.0.0.1',0,'PostmanRuntime/7.28.4',1642422621,'用户登录系统'),(1000005,1,'13911112222','/api/v1/login/login','127.0.0.1',0,'PostmanRuntime/7.28.4',1642422845,'用户登录系统'),(1000006,1,'13911112222','/api/v1/login/login','127.0.0.1',0,'PostmanRuntime/7.28.4',1642423028,'用户登录系统'),(1000007,1,'13911112222','/api/v1/login/login','127.0.0.1',0,'PostmanRuntime/7.28.4',1642423227,'用户登录系统'),(1000008,1,'13911112222','/api/v1/login/login','127.0.0.1',0,'PostmanRuntime/7.28.4',1642470928,'用户登录系统'),(1000009,1,'13911112222','/api/v1/login/login','127.0.0.1',0,'PostmanRuntime/7.28.4',1642470976,'用户登录系统'),(1000010,1,'13911112222','/api/v1/login/login','127.0.0.1',0,'PostmanRuntime/7.28.4',1642471007,'用户登录系统'),(1000011,1,'13911112222','/api/v1/login/login','127.0.0.1',0,'PostmanRuntime/7.28.4',1642516150,'用户登录系统');
/*!40000 ALTER TABLE `user_login_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_vip_log`
--

DROP TABLE IF EXISTS `user_vip_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_vip_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `trade_no` varchar(255) DEFAULT NULL COMMENT '订单编号',
  `uid` int DEFAULT '0' COMMENT '用户id',
  `vip_id` int DEFAULT '0' COMMENT 'VIPID',
  `grade` int DEFAULT '0' COMMENT '等级',
  `type` tinyint DEFAULT '1' COMMENT '类型：1购买vip 2订单金额升级',
  `pay_type` tinyint DEFAULT '1' COMMENT '1余额支付 2支付宝支付 3银行卡支付',
  `money` decimal(8,2) DEFAULT '0.00' COMMENT '金额',
  `tid` int DEFAULT '0' COMMENT '对应的id 订单id 支付宝id 银行卡id',
  `pay_status` tinyint DEFAULT '0' COMMENT '0待支付 1已支付',
  `status` tinyint DEFAULT '0' COMMENT '0未提交凭证 1通过 2拒绝 3凭证审核中',
  `img` varchar(255) NOT NULL DEFAULT 'none' COMMENT '支付凭证',
  `create_time` int DEFAULT '0',
  `update_time` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_vip_log`
--

LOCK TABLES `user_vip_log` WRITE;
/*!40000 ALTER TABLE `user_vip_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_vip_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_welfare_log`
--

DROP TABLE IF EXISTS `user_welfare_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_welfare_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL COMMENT '用户id号',
  `welfare_id` int NOT NULL DEFAULT '0' COMMENT '奖品id',
  `welfare_name` varchar(255) NOT NULL DEFAULT '' COMMENT '中奖名称',
  `remark` varchar(255) NOT NULL DEFAULT '1' COMMENT '状态1待审2通过',
  `create_time` datetime DEFAULT NULL COMMENT '中奖时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_welfare_log`
--

LOCK TABLES `user_welfare_log` WRITE;
/*!40000 ALTER TABLE `user_welfare_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_welfare_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_withdraw`
--

DROP TABLE IF EXISTS `user_withdraw`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_withdraw` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id号',
  `money` double(20,2) NOT NULL DEFAULT '0.00' COMMENT '提现金额',
  `username` varchar(20) NOT NULL COMMENT '银行卡持卡人',
  `address` varchar(255) NOT NULL COMMENT '银行卡所在地',
  `bank_name` varchar(50) NOT NULL COMMENT '银行名称',
  `bank_num` varchar(50) NOT NULL COMMENT '银行卡号',
  `bank_open` varchar(255) NOT NULL COMMENT '开户行',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未审核，1审核通过，2审核不通过',
  `bz` varchar(255) NOT NULL COMMENT '备注',
  `type` tinyint NOT NULL DEFAULT '1' COMMENT '1银行卡提现 2支付宝',
  `trade_no` int NOT NULL DEFAULT '0',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '最后修改',
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '姓名',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`),
  KEY `status` (`status`),
  KEY `user_withdraw_username_idx` (`username`),
  KEY `user_withdraw_status_idx` (`status`),
  KEY `user_withdraw_type_idx` (`type`),
  KEY `user_withdraw_trade_no_idx` (`trade_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_withdraw`
--

LOCK TABLES `user_withdraw` WRITE;
/*!40000 ALTER TABLE `user_withdraw` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_withdraw` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `version_upgrade`
--

DROP TABLE IF EXISTS `version_upgrade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `version_upgrade` (
  `id` int NOT NULL AUTO_INCREMENT,
  `version_code` varchar(50) NOT NULL COMMENT '版本标识',
  `type` tinyint NOT NULL COMMENT '是否上级1升级2强制升级3不升级',
  `apk_url` varchar(255) NOT NULL COMMENT '路径',
  `upgrade_point` varchar(255) NOT NULL COMMENT '更新提示',
  `create_time` int NOT NULL,
  `update_time` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `version_upgrade`
--

LOCK TABLES `version_upgrade` WRITE;
/*!40000 ALTER TABLE `version_upgrade` DISABLE KEYS */;
INSERT INTO `version_upgrade` VALUES (1,'1.3.1',1,'https://yjhpack.oss-cn-beijing.aliyuncs.com/apk20211112180400.apk','修复已知BUGs23',1,1642755657);
/*!40000 ALTER TABLE `version_upgrade` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vip`
--

DROP TABLE IF EXISTS `vip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vip` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'vip名称',
  `grade` int NOT NULL COMMENT 'vip等级',
  `rate` int NOT NULL DEFAULT '0' COMMENT '返佣比例',
  `price` decimal(8,2) DEFAULT '0.00' COMMENT '价格',
  `order_price` int DEFAULT '0' COMMENT '订单金额',
  `create_time` int DEFAULT '0',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `grade` (`grade`) USING BTREE,
  KEY `vip_name_idx` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT COMMENT='vip表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vip`
--

LOCK TABLES `vip` WRITE;
/*!40000 ALTER TABLE `vip` DISABLE KEYS */;
INSERT INTO `vip` VALUES (1,'vip1',1,1,5000.00,100000,1621927572,0),(2,'VIP2',2,2,8000.00,200000,1622007818,0),(3,'VIP3',3,3,10000.00,300000,1622013929,0);
/*!40000 ALTER TABLE `vip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `walfare`
--

DROP TABLE IF EXISTS `walfare`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `walfare` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rate` int NOT NULL DEFAULT '0' COMMENT '中奖概率',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '奖品名称',
  `type` tinyint DEFAULT '1' COMMENT '类型：3实物、2产品、1红包',
  `money` decimal(8,2) DEFAULT '0.00' COMMENT '红包金额',
  `color` varchar(255) NOT NULL DEFAULT '' COMMENT '转盘背景颜色',
  `create_time` int NOT NULL,
  `remark` varchar(256) NOT NULL DEFAULT '',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '最后修改',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `walfare_type_idx` (`type`),
  KEY `walfare_create_time_idx` (`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `walfare`
--

LOCK TABLES `walfare` WRITE;
/*!40000 ALTER TABLE `walfare` DISABLE KEYS */;
INSERT INTO `walfare` VALUES (1,1,'特等奖：现金红包888元',1,888.00,'#9F79EE',1552270187,'',0),(2,40,'安慰奖：理财红包8.88元',1,8.88,'#CDB5CD',1552272451,'',0),(3,5,'二等奖：理财红包288元',1,288.00,'#CD950C',1552272570,'',0),(4,1,'三等奖：手机充值200元',1,200.00,'#CD919E',1552272589,'',0),(6,12,'幸运奖：现金红包50元',1,50.00,'#C0FF3E',1552272621,'',0),(9,1,'一等奖：手机充值500元',1,500.00,'#EEC900',1554650119,'',0),(11,40,'安慰奖：理财红包8.88元',1,8.88,'#FFA07A',1554652138,'',0);
/*!40000 ALTER TABLE `walfare` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-03-04 18:05:01
