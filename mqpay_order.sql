-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2023-02-25 15:01:02
-- 服务器版本： 5.7.34-log
-- PHP 版本： 7.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `tankingdb`
--

-- --------------------------------------------------------

--
-- 表的结构 `mqpay_order`
--

CREATE TABLE `mqpay_order` (
  `id` int(5) NOT NULL COMMENT 'id',
  `order_num` varchar(32) DEFAULT NULL COMMENT '订单号',
  `order_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `order_price` varchar(10) DEFAULT NULL COMMENT '订单价格（真实价格）',
  `order_money` varchar(10) DEFAULT NULL COMMENT '订单价格（支付金额）',
  `order_paytime` varchar(32) DEFAULT NULL COMMENT '支付时间',
  `order_status` int(2) NOT NULL DEFAULT '1' COMMENT '支付状态（1未支付 2已支付）',
  `order_msg` text COMMENT '原文'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转储表的索引
--

--
-- 表的索引 `mqpay_order`
--
ALTER TABLE `mqpay_order`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `mqpay_order`
--
ALTER TABLE `mqpay_order`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT COMMENT 'id';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
