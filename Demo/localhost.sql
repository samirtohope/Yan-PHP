SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- 数据库: `kakalong`
--
CREATE DATABASE `kakalong` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `kakalong`;

-- --------------------------------------------------------

--
-- 表的结构 `kakalong_action`
--

CREATE TABLE IF NOT EXISTS `kakalong_action` (
  `actionid` char(9) NOT NULL,
  `parentid` char(9) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `public` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`actionid`),
  KEY `parentid` (`parentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 导出表中的数据 `kakalong_action`
--

INSERT INTO `kakalong_action` (`actionid`, `parentid`, `name`, `public`) VALUES
('001000000', NULL, '系统', 0),
('001001000', '001000000', 'Index', 0),
('001001001', '001001000', '首页', 1),
('002000000', NULL, 'Twitter', 0),
('002001000', '002000000', 'Index', 0),
('002001001', '002001000', '首页', 1),
('002001002', '002001000', '文档', 1),
('002001003', '002001000', '写', 0),
('003000000', NULL, '快速开始', 0);

-- --------------------------------------------------------

--
-- 表的结构 `kakalong_action_has_access`
--

CREATE TABLE IF NOT EXISTS `kakalong_action_has_access` (
  `actionid` char(9) NOT NULL,
  `access` varchar(50) NOT NULL,
  PRIMARY KEY (`actionid`,`access`),
  KEY `actionid` (`actionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 导出表中的数据 `kakalong_action_has_access`
--

INSERT INTO `kakalong_action_has_access` (`actionid`, `access`) VALUES
('001000000', 'system'),
('001001000', 'system.index'),
('001001001', 'system.index.index'),
('001001001', 'system.index.menu'),
('002000000', 'twitter'),
('002001000', 'twitter.index'),
('002001001', 'twitter.index.index'),
('002001001', 'twitter.index.page'),
('002001002', 'twitter.index.doc'),
('002001003', 'twitter.index.del'),
('002001003', 'twitter.index.edit'),
('002001003', 'twitter.index.write'),
('003000000', 'start');

-- --------------------------------------------------------

--
-- 表的结构 `kakalong_role`
--

CREATE TABLE IF NOT EXISTS `kakalong_role` (
  `roleid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `parentid` mediumint(8) unsigned DEFAULT NULL,
  PRIMARY KEY (`roleid`),
  KEY `parentid` (`parentid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- 导出表中的数据 `kakalong_role`
--

INSERT INTO `kakalong_role` (`roleid`, `name`, `parentid`) VALUES
(1, '管理员', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `kakalong_role_has_action`
--

CREATE TABLE IF NOT EXISTS `kakalong_role_has_action` (
  `roleid` mediumint(8) unsigned NOT NULL,
  `actionid` char(9) NOT NULL,
  PRIMARY KEY (`roleid`,`actionid`),
  KEY `roleid` (`roleid`),
  KEY `actionid` (`actionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 导出表中的数据 `kakalong_role_has_action`
--

INSERT INTO `kakalong_role_has_action` (`roleid`, `actionid`) VALUES
(1, '001000000'),
(1, '002000000'),
(1, '003000000');

-- --------------------------------------------------------

--
-- 表的结构 `kakalong_user`
--

CREATE TABLE IF NOT EXISTS `kakalong_user` (
  `userid` int(10) unsigned NOT NULL,
  `groupid` mediumint(8) unsigned NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` char(32) NOT NULL,
  `username` varchar(20) NOT NULL,
  `disabled` tinyint(1) unsigned DEFAULT NULL,
  `created` int(10) unsigned NOT NULL,
  `updated` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`userid`),
  UNIQUE KEY `email` (`email`),
  KEY `groupid` (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 导出表中的数据 `kakalong_user`
--

INSERT INTO `kakalong_user` (`userid`, `groupid`, `email`, `password`, `username`, `disabled`, `created`, `updated`) VALUES
(1, 1, 'me@yanbingbing.com', '4297f44b13955235245b2497399d7a93', 'kakalong', NULL, 1335613900, 1335613900);

-- --------------------------------------------------------

--
-- 表的结构 `kakalong_user_has_role`
--

CREATE TABLE IF NOT EXISTS `kakalong_user_has_role` (
  `userid` int(10) unsigned NOT NULL,
  `roleid` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`userid`,`roleid`),
  KEY `userid` (`userid`),
  KEY `roleid` (`roleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 导出表中的数据 `kakalong_user_has_role`
--

INSERT INTO `kakalong_user_has_role` (`userid`, `roleid`) VALUES
(1, 1);

--
-- 限制导出的表
--

--
-- 限制表 `kakalong_action`
--
ALTER TABLE `kakalong_action`
  ADD CONSTRAINT `action_ibfk_1` FOREIGN KEY (`parentid`) REFERENCES `kakalong_action` (`actionid`) ON DELETE CASCADE;

--
-- 限制表 `kakalong_action_has_access`
--
ALTER TABLE `kakalong_action_has_access`
  ADD CONSTRAINT `action_has_access_ibfk_1` FOREIGN KEY (`actionid`) REFERENCES `kakalong_action` (`actionid`) ON DELETE CASCADE;

--
-- 限制表 `kakalong_role`
--
ALTER TABLE `kakalong_role`
  ADD CONSTRAINT `role_ibfk_1` FOREIGN KEY (`parentid`) REFERENCES `kakalong_role` (`roleid`) ON DELETE CASCADE;

--
-- 限制表 `kakalong_role_has_action`
--
ALTER TABLE `kakalong_role_has_action`
  ADD CONSTRAINT `role_has_action_ibfk_1` FOREIGN KEY (`roleid`) REFERENCES `kakalong_role` (`roleid`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_action_ibfk_2` FOREIGN KEY (`actionid`) REFERENCES `kakalong_action` (`actionid`) ON DELETE CASCADE;

--
-- 限制表 `kakalong_user_has_role`
--
ALTER TABLE `kakalong_user_has_role`
  ADD CONSTRAINT `user_has_role_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `kakalong_user` (`userid`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_has_role_ibfk_2` FOREIGN KEY (`roleid`) REFERENCES `kakalong_role` (`roleid`) ON DELETE CASCADE;