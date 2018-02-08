/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50553
Source Host           : 127.0.0.1:3306
Source Database       : thinkssycms

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2018-02-08 14:45:43
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for yt_admins
-- ----------------------------
DROP TABLE IF EXISTS `yt_admins`;
CREATE TABLE `yt_admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) DEFAULT NULL COMMENT '用户名',
  `password` varchar(50) DEFAULT NULL COMMENT '密码',
  `roleid` int(4) DEFAULT NULL COMMENT '角色ID',
  `loginip` varchar(20) DEFAULT NULL COMMENT '登录IP',
  `regtime` datetime DEFAULT NULL COMMENT '注册时间',
  `state` int(1) DEFAULT NULL COMMENT '用户状态',
  `email` varchar(50) DEFAULT NULL,
  `nickname` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_users` (`username`) USING BTREE,
  KEY `index_roleid` (`roleid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='系统用户表\r\n';

-- ----------------------------
-- Records of yt_admins
-- ----------------------------
INSERT INTO `yt_admins` VALUES ('1', 'admin', '5754296be74096a2504e772056a6c3c3', '1', '127.0.0.1', null, '1', null, '舒思远');
INSERT INTO `yt_admins` VALUES ('2', 'shusiyuan', 'e10adc3949ba59abbe56e057f20f883e', '1', '127.0.0.1', '2017-09-21 23:23:00', '0', null, '舒思远');
INSERT INTO `yt_admins` VALUES ('3', 'ytlyb', 'e10adc3949ba59abbe56e057f20f883e', '2', '118.249.101.241', '2017-12-06 18:43:56', '1', null, null);
INSERT INTO `yt_admins` VALUES ('4', 'ytzyp', '5754296be74096a2504e772056a6c3c3', '2', '118.249.101.241', '2017-12-06 18:44:55', '1', null, null);
INSERT INTO `yt_admins` VALUES ('5', 'ytcaiwu', 'e10adc3949ba59abbe56e057f20f883e', '6', '118.249.101.241', '2017-12-06 18:45:38', '1', null, null);
INSERT INTO `yt_admins` VALUES ('6', 'ytkefu', 'e10adc3949ba59abbe56e057f20f883e', '7', '118.249.101.241', '2017-12-06 18:51:40', '1', null, null);
INSERT INTO `yt_admins` VALUES ('7', 'ytdjs', 'e10adc3949ba59abbe56e057f20f883e', '2', '118.249.120.154', '2017-12-08 20:50:11', '1', null, null);
INSERT INTO `yt_admins` VALUES ('8', 'ytzpl', 'e10adc3949ba59abbe56e057f20f883e', '2', '118.249.123.57', '2017-12-20 10:56:33', '1', null, null);
INSERT INTO `yt_admins` VALUES ('9', 'ytyujiao', 'e10adc3949ba59abbe56e057f20f883e', '2', '118.249.123.57', '2017-12-18 17:31:54', '1', null, null);
INSERT INTO `yt_admins` VALUES ('10', 'ytzqh', 'e10adc3949ba59abbe56e057f20f883e', '7', '118.249.120.154', '2017-12-09 09:50:11', '1', null, null);
INSERT INTO `yt_admins` VALUES ('11', 'ytwangjing', 'e10adc3949ba59abbe56e057f20f883e', '2', '118.249.100.79', '2017-12-22 11:59:42', '1', null, null);
INSERT INTO `yt_admins` VALUES ('12', 'ytliulitian', 'e10adc3949ba59abbe56e057f20f883e', '6', '118.249.103.103', '2017-12-28 09:18:59', '1', null, null);
INSERT INTO `yt_admins` VALUES ('13', 'ytxiaojinfeng', 'e10adc3949ba59abbe56e057f20f883e', '6', '118.249.103.103', '2017-12-28 09:19:47', '1', null, null);
INSERT INTO `yt_admins` VALUES ('14', 'ytzhouxia', 'e10adc3949ba59abbe56e057f20f883e', '2', '118.249.103.103', '2017-12-28 09:49:55', '1', null, null);
INSERT INTO `yt_admins` VALUES ('15', 'ytyujielin', 'e10adc3949ba59abbe56e057f20f883e', '2', '118.249.103.103', '2017-12-28 09:50:31', '1', null, null);
INSERT INTO `yt_admins` VALUES ('16', 'ytlengjuan', 'e10adc3949ba59abbe56e057f20f883e', '2', '118.249.103.103', '2017-12-28 09:52:36', '1', null, null);

-- ----------------------------
-- Table structure for yt_fields
-- ----------------------------
DROP TABLE IF EXISTS `yt_fields`;
CREATE TABLE `yt_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modelid` int(11) DEFAULT NULL COMMENT '模型编号',
  `fieldname` varchar(50) DEFAULT NULL COMMENT '字段名称',
  `fieldtype` varchar(50) DEFAULT NULL COMMENT '字段类型',
  `fieldvalue` varchar(50) DEFAULT NULL COMMENT '字段默认值',
  `fielddiscribe` text COMMENT '字段描述',
  `fieldrelation` varchar(120) DEFAULT NULL COMMENT '和其他表之间的关系',
  `isindex` tinyint(1) NOT NULL DEFAULT '2' COMMENT '是否索引  【1表示是  2表示否】',
  `isempty` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否允许为空  1 允许， 2不允许',
  PRIMARY KEY (`id`),
  KEY `index_model` (`modelid`),
  KEY `index_title` (`fieldname`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='模型字段表';

-- ----------------------------
-- Records of yt_fields
-- ----------------------------
INSERT INTO `yt_fields` VALUES ('1', '1', 'title', 'varchar(60)', '', '文章标题', '暂无', '3', '2');
INSERT INTO `yt_fields` VALUES ('2', '1', 'seokeyword', 'text', '', 'seo关键字', '暂无', '2', '2');
INSERT INTO `yt_fields` VALUES ('3', '1', 'sendtime', 'datetime', '', '发布时间', '暂无', '2', '1');

-- ----------------------------
-- Table structure for yt_files
-- ----------------------------
DROP TABLE IF EXISTS `yt_files`;
CREATE TABLE `yt_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `savepath` varchar(150) NOT NULL,
  `md5name` varchar(32) NOT NULL,
  `filename` varchar(100) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `oldfilename` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `indexmd5name` (`md5name`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yt_files
-- ----------------------------

-- ----------------------------
-- Table structure for yt_models
-- ----------------------------
DROP TABLE IF EXISTS `yt_models`;
CREATE TABLE `yt_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modelname` varchar(150) DEFAULT NULL COMMENT '模型名称',
  `tablename` varchar(150) DEFAULT NULL COMMENT '模型名称',
  `modeldiscribe` text COMMENT '模型描述',
  `state` int(11) DEFAULT NULL COMMENT '模型状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='模型表';

-- ----------------------------
-- Records of yt_models
-- ----------------------------
INSERT INTO `yt_models` VALUES ('1', '新闻模型', 'yt_news', '新闻类，文章模型', '1');

-- ----------------------------
-- Table structure for yt_news
-- ----------------------------
DROP TABLE IF EXISTS `yt_news`;
CREATE TABLE `yt_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(60) DEFAULT NULL COMMENT '文章标题',
  `seokeyword` text COMMENT 'seo关键字',
  `sendtime` datetime NOT NULL COMMENT '发布时间',
  PRIMARY KEY (`id`),
  KEY `index_title` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='新闻模型';

-- ----------------------------
-- Records of yt_news
-- ----------------------------

-- ----------------------------
-- Table structure for yt_permission
-- ----------------------------
DROP TABLE IF EXISTS `yt_permission`;
CREATE TABLE `yt_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permname` varchar(50) DEFAULT NULL COMMENT '权限名称',
  `permurl` varchar(100) DEFAULT NULL COMMENT '权限管理URL',
  `permcss` varchar(20) DEFAULT NULL COMMENT '前端css名称',
  `parentid` int(11) DEFAULT NULL COMMENT '权限父集',
  `level` int(1) DEFAULT NULL COMMENT '菜单深度(从零开始)',
  `status` int(1) DEFAULT NULL COMMENT '状态（1有效，0无效）',
  `remark` varchar(200) DEFAULT NULL COMMENT '权限说明',
  `actiontype` varchar(2) DEFAULT NULL COMMENT '操作类型（面板操作，行内操作）',
  `jumpway` int(1) NOT NULL DEFAULT '1' COMMENT '跳转方式，1打开新窗口 ，2询问后打开窗口，3ajax链接，无需打开窗口 【静默无弹窗】 4：ajax询问链接，无需弹窗  5：普通跳转',
  `jumpask` text COMMENT '询问类型弹窗，提示语',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=80 DEFAULT CHARSET=utf8 COMMENT='权限表';

-- ----------------------------
-- Records of yt_permission
-- ----------------------------
INSERT INTO `yt_permission` VALUES ('1', '管理员管理', '', '&#xe62d;', '0', '0', '1', '系统管理员菜具备的权限', null, '1', null);
INSERT INTO `yt_permission` VALUES ('2', '角色管理', 'admin/role/index', '', '1', '1', '1', '系统管理员菜具备的权限', null, '1', null);
INSERT INTO `yt_permission` VALUES ('3', '权限管理', 'admin/role/roleperm', '', '1', '1', '1', '系统管理员菜具备的权限', null, '1', '');
INSERT INTO `yt_permission` VALUES ('4', '管理员列表', 'role/adminlist', '', '1', '1', '1', '系统管理员菜具备的权限', null, '1', null);
INSERT INTO `yt_permission` VALUES ('5', '添加角色', 'admin/role/roleedit', '&#xe600;', '2', '2', '1', '系统管理员菜具备的权限', '1', '1', null);
INSERT INTO `yt_permission` VALUES ('6', '编辑角色', 'admin/role/roleedit', '&#xe6df;', '2', '2', '1', '系统管理员菜具备的权限', '2', '1', null);
INSERT INTO `yt_permission` VALUES ('7', '代理商管理', '', '', '0', '0', '1', '前端代理商管理', null, '1', null);
INSERT INTO `yt_permission` VALUES ('8', '编辑', 'role/manageperm', '&#xe6df;', '3', '2', '1', '编辑权限', '2', '1', null);
INSERT INTO `yt_permission` VALUES ('9', '设置权限', 'admin/role/allotperm', '', '2', '2', '1', '设置权限', '2', '1', null);
INSERT INTO `yt_permission` VALUES ('10', '添加权限', 'role/manageperm', '&#xe600;', '3', '2', '1', '添加权限', '1', '1', null);
INSERT INTO `yt_permission` VALUES ('12', '添加管理员', 'role/addadmin', '', '4', '2', '1', '添加管理员', '1', '1', '');
INSERT INTO `yt_permission` VALUES ('13', '编辑', 'admin/role/addadmin', '&#xe6df;', '4', '2', '1', '编辑用户', '2', '1', null);
INSERT INTO `yt_permission` VALUES ('14', '系统设置', '', '&#xe62e;', '0', '0', '1', '系统设置', null, '1', null);
INSERT INTO `yt_permission` VALUES ('15', '基本设置', 'admin/websystem/index', '', '14', '1', '1', '网站基本设置', null, '1', '');
INSERT INTO `yt_permission` VALUES ('76', '模型管理', 'admin/modelbuild/index', '', '14', '1', '1', '模型管理', null, '1', null);
INSERT INTO `yt_permission` VALUES ('77', '模型字段', 'modelbuild/fields', '', '76', '2', '1', '增模型字段', '2', '1', '');
INSERT INTO `yt_permission` VALUES ('78', '添加模型', 'modelbuild/addmodel', '', '76', '2', '1', '新增模型', '1', '1', '');
INSERT INTO `yt_permission` VALUES ('79', '编辑模型', 'modelbuild/addmodel', '&#xe60c;', '76', '2', '1', '编辑模型，修改模型', '2', '1', '');

-- ----------------------------
-- Table structure for yt_rolemap
-- ----------------------------
DROP TABLE IF EXISTS `yt_rolemap`;
CREATE TABLE `yt_rolemap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roleid` int(2) DEFAULT NULL COMMENT '角色Id',
  `permid` int(2) DEFAULT NULL COMMENT '权限ID',
  `status` int(1) DEFAULT '1' COMMENT '是否有效  1有效，0无效',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=211 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yt_rolemap
-- ----------------------------
INSERT INTO `yt_rolemap` VALUES ('1', '1', '1', '1');
INSERT INTO `yt_rolemap` VALUES ('2', '1', '2', '1');
INSERT INTO `yt_rolemap` VALUES ('29', '1', '8', '1');
INSERT INTO `yt_rolemap` VALUES ('28', '1', '3', '1');
INSERT INTO `yt_rolemap` VALUES ('39', '1', '10', '1');
INSERT INTO `yt_rolemap` VALUES ('19', '1', '3', '1');
INSERT INTO `yt_rolemap` VALUES ('36', '1', '6', '1');
INSERT INTO `yt_rolemap` VALUES ('15', '1', '9', '1');
INSERT INTO `yt_rolemap` VALUES ('14', '1', '7', '1');
INSERT INTO `yt_rolemap` VALUES ('38', '1', '5', '1');
INSERT INTO `yt_rolemap` VALUES ('37', '1', '4', '1');
INSERT INTO `yt_rolemap` VALUES ('40', '1', '12', '1');
INSERT INTO `yt_rolemap` VALUES ('41', '1', '13', '1');
INSERT INTO `yt_rolemap` VALUES ('42', '1', '14', '1');
INSERT INTO `yt_rolemap` VALUES ('43', '1', '15', '1');
INSERT INTO `yt_rolemap` VALUES ('210', '1', '79', '1');
INSERT INTO `yt_rolemap` VALUES ('209', '1', '78', '1');
INSERT INTO `yt_rolemap` VALUES ('208', '1', '77', '1');
INSERT INTO `yt_rolemap` VALUES ('207', '1', '76', '1');

-- ----------------------------
-- Table structure for yt_roles
-- ----------------------------
DROP TABLE IF EXISTS `yt_roles`;
CREATE TABLE `yt_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rolename` varchar(50) DEFAULT NULL COMMENT '角色名称',
  `state` int(1) DEFAULT NULL COMMENT '是否有效',
  `remark` varchar(200) DEFAULT NULL COMMENT '角色备注说明',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='角色表';

-- ----------------------------
-- Records of yt_roles
-- ----------------------------
INSERT INTO `yt_roles` VALUES ('1', '系统管理组', '1', '拥有所有的权限');
INSERT INTO `yt_roles` VALUES ('2', '报单后台', '1', '发布资讯信息');
INSERT INTO `yt_roles` VALUES ('3', '测试组', '1', '专用于测试');
INSERT INTO `yt_roles` VALUES ('6', '财务', '1', '');
INSERT INTO `yt_roles` VALUES ('7', '客服', '1', '');

-- ----------------------------
-- Table structure for yt_website
-- ----------------------------
DROP TABLE IF EXISTS `yt_website`;
CREATE TABLE `yt_website` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `versions` varchar(20) DEFAULT NULL COMMENT '当前版本号',
  `websitename` varchar(100) DEFAULT NULL COMMENT '网站名称',
  `webkeyword` varchar(100) DEFAULT NULL COMMENT '网站关键字',
  `webdisciption` varchar(255) DEFAULT NULL COMMENT '网站描述',
  `uploaddir` varchar(100) DEFAULT NULL COMMENT '上传文件保存路径',
  `footver` varchar(100) DEFAULT NULL COMMENT '底部版权信息',
  `webicp` varchar(100) DEFAULT NULL COMMENT '备案号',
  `isopen` int(1) DEFAULT NULL COMMENT '是否休市  1正常， 0休市',
  `recome` varchar(6) DEFAULT NULL COMMENT '前端注册推荐码',
  `excelmuban` varchar(100) DEFAULT NULL COMMENT '对账模板',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yt_website
-- ----------------------------
INSERT INTO `yt_website` VALUES ('1', '2.0', '云堂名车行团购系统', '云堂名车行团购系统', '云堂名车行团购系统', 'uploadfile', '©200011112211', 'ICP000000000001', '1', '601231', 'Upload/excel/2017-11-21/1511235583.xls');
