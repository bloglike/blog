/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : blog

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2017-05-16 16:45:59
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for lwh_admin_user
-- ----------------------------
DROP TABLE IF EXISTS `lwh_admin_user`;
CREATE TABLE `lwh_admin_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(11) NOT NULL,
  `password` varchar(64) NOT NULL,
  `salt_1` int(1) unsigned NOT NULL,
  `salt_2` int(1) unsigned NOT NULL,
  `headimg` varchar(64) DEFAULT NULL COMMENT '用户头像',
  `alias` varchar(32) NOT NULL COMMENT '昵称',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '权限等级 1 BOSS 2 主管  3经理   4 队长 5 职工',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '账号状态 1开启 2禁用 ',
  `type` enum('2','1') NOT NULL DEFAULT '1' COMMENT '1会员 2 管理',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lwh_admin_user
-- ----------------------------
INSERT INTO `lwh_admin_user` VALUES ('1', '18795409635', '61a2c5b8807a8f66045cbdf193a5b16a', '1314520', '5201314', null, '', '0', '1', '2');
INSERT INTO `lwh_admin_user` VALUES ('2', '13261072316', '61a2c5b8807a8f66045cbdf193a5b16a', '1314520', '5201314', null, '', '0', '1', '2');

-- ----------------------------
-- Table structure for lwh_blog
-- ----------------------------
DROP TABLE IF EXISTS `lwh_blog`;
CREATE TABLE `lwh_blog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `content` text NOT NULL COMMENT '博文',
  `type` tinyint(1) unsigned NOT NULL COMMENT '博文类型',
  `addtime` int(2) unsigned NOT NULL COMMENT '添加时间',
  `heat` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '热度:点击量',
  `fabulous` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞',
  `leaving` int(10) unsigned NOT NULL COMMENT '回复量',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lwh_blog
-- ----------------------------

-- ----------------------------
-- Table structure for lwh_comments
-- ----------------------------
DROP TABLE IF EXISTS `lwh_comments`;
CREATE TABLE `lwh_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL COMMENT '发送者id',
  `tid` int(10) unsigned NOT NULL COMMENT '接收者id',
  `state` tinyint(1) unsigned NOT NULL COMMENT '发送类型',
  `content` varchar(255) NOT NULL COMMENT '消息内容',
  `read` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已读0未读1已读',
  `parent_id` int(8) unsigned NOT NULL DEFAULT '0' COMMENT '父评论id',
  `addtime` int(2) unsigned NOT NULL COMMENT '添加时间',
  `heat` int(10) unsigned DEFAULT NULL COMMENT '热度:回帖量',
  `fabulous` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞量',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lwh_comments
-- ----------------------------

-- ----------------------------
-- Table structure for lwh_controller
-- ----------------------------
DROP TABLE IF EXISTS `lwh_controller`;
CREATE TABLE `lwh_controller` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `controller` varchar(64) NOT NULL COMMENT '控制器名',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lwh_controller
-- ----------------------------

-- ----------------------------
-- Table structure for lwh_friends
-- ----------------------------
DROP TABLE IF EXISTS `lwh_friends`;
CREATE TABLE `lwh_friends` (
  `hostid` int(10) unsigned NOT NULL COMMENT '主人ID',
  `friendid` int(10) unsigned NOT NULL COMMENT '好友ID',
  `addtime` int(2) unsigned NOT NULL COMMENT '添加时间',
  KEY `uid` (`hostid`) USING BTREE,
  KEY `fid` (`friendid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lwh_friends
-- ----------------------------

-- ----------------------------
-- Table structure for lwh_function
-- ----------------------------
DROP TABLE IF EXISTS `lwh_function`;
CREATE TABLE `lwh_function` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `function` varchar(64) NOT NULL COMMENT '方法名',
  `conid` int(10) unsigned NOT NULL COMMENT '控制器id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lwh_function
-- ----------------------------

-- ----------------------------
-- Table structure for lwh_grade
-- ----------------------------
DROP TABLE IF EXISTS `lwh_grade`;
CREATE TABLE `lwh_grade` (
  `grade` tinyint(1) unsigned NOT NULL COMMENT '管理等级',
  `funid` int(5) unsigned NOT NULL COMMENT '方法id',
  `conid` int(5) unsigned NOT NULL COMMENT '控制器id',
  KEY `gd` (`grade`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lwh_grade
-- ----------------------------

-- ----------------------------
-- Table structure for lwh_user
-- ----------------------------
DROP TABLE IF EXISTS `lwh_user`;
CREATE TABLE `lwh_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `password` varchar(64) NOT NULL COMMENT '用户密码',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '用户等级(1-20级);',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '用户状态:1禁用:2启用:3销毁',
  `salt` tinyint(4) unsigned NOT NULL COMMENT '加密字串',
  `user_name` varchar(64) NOT NULL COMMENT '登录账号  手机或邮箱',
  `phone` int(2) unsigned DEFAULT NULL COMMENT '手机',
  `email` varchar(64) DEFAULT NULL COMMENT '邮箱',
  `addtime` int(2) unsigned NOT NULL COMMENT '注册时间',
  `uptime` int(2) unsigned DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `use` (`id`,`phone`,`email`,`user_name`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lwh_user
-- ----------------------------

-- ----------------------------
-- Table structure for lwh_user_info
-- ----------------------------
DROP TABLE IF EXISTS `lwh_user_info`;
CREATE TABLE `lwh_user_info` (
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `headimg` varchar(64) DEFAULT NULL COMMENT '用户头像',
  `sex` enum('2','1') NOT NULL DEFAULT '1' COMMENT '性别 1男 2 女',
  `qq` int(2) unsigned DEFAULT NULL COMMENT 'qq',
  `wx` varchar(64) DEFAULT NULL COMMENT '微信',
  `brief` varchar(100) DEFAULT '这个家伙很懒什么也没留下' COMMENT '个人简介',
  `company` varchar(64) DEFAULT NULL COMMENT '公司',
  `dfbirth` int(1) unsigned DEFAULT '1111111111' COMMENT '出生日期',
  `motime` int(2) unsigned DEFAULT NULL COMMENT '修改时间',
  `alias` varchar(32) DEFAULT NULL COMMENT '昵称',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lwh_user_info
-- ----------------------------

-- ----------------------------
-- Table structure for lwh_user_log
-- ----------------------------
DROP TABLE IF EXISTS `lwh_user_log`;
CREATE TABLE `lwh_user_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `event` varchar(255) NOT NULL COMMENT '事件内容',
  `title` varchar(64) NOT NULL COMMENT '事件主题',
  `addtime` int(2) unsigned NOT NULL COMMENT '添加时间',
  `type` enum('2','1') NOT NULL DEFAULT '1' COMMENT '1会员 2 管理员',
  `address_ip` varchar(30) NOT NULL COMMENT 'ip',
  `address_name` varchar(64) NOT NULL COMMENT '地址',
  PRIMARY KEY (`id`),
  KEY `uid` (`user_id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lwh_user_log
-- ----------------------------
INSERT INTO `lwh_user_log` VALUES ('1', '1', '成功登录后台!', '管理员登录', '1494898419', '1', '127.0.0.1', '未知地区');
INSERT INTO `lwh_user_log` VALUES ('2', '1', '成功登录后台!', '管理员登录', '1494899426', '1', '127.0.0.1', '未知地区');
INSERT INTO `lwh_user_log` VALUES ('3', '1', '成功登录后台!', '管理员登录', '1494899545', '1', '127.0.0.1', '未知地区');
INSERT INTO `lwh_user_log` VALUES ('4', '1', '成功登录后台!', '管理员登录', '1494900485', '1', '127.0.0.1', '未知地区');
INSERT INTO `lwh_user_log` VALUES ('5', '1', '成功登录后台!', '管理员登录', '1494901543', '1', '127.0.0.1', '未知地区');
INSERT INTO `lwh_user_log` VALUES ('6', '2', '成功登录后台!', '管理员登录', '1494901562', '1', '127.0.0.1', '未知地区');
INSERT INTO `lwh_user_log` VALUES ('7', '1', '成功登录后台!', '管理员登录', '1494918289', '1', '127.0.0.1', '未知地区');
INSERT INTO `lwh_user_log` VALUES ('8', '1', '成功登录后台!', '管理员登录', '1494918659', '1', '127.0.0.1', '未知地区');

-- ----------------------------
-- Table structure for lwh_user_session
-- ----------------------------
DROP TABLE IF EXISTS `lwh_user_session`;
CREATE TABLE `lwh_user_session` (
  `session_id` varchar(64) NOT NULL COMMENT 'session_id()',
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `userid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sid` (`session_id`) USING BTREE,
  UNIQUE KEY `uid` (`userid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lwh_user_session
-- ----------------------------
INSERT INTO `lwh_user_session` VALUES ('t4hq7g7eju817gbn5n1nk2fn05', '8', '1');
