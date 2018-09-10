/*
Navicat MySQL Data Transfer

Source Server         : Live
Source Server Version : 50173
Source Host           : localhost:3306
Source Database       : xs_new_oms

Target Server Type    : MYSQL
Target Server Version : 50173
File Encoding         : 65001

Date: 2018-01-12 15:52:38
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for amazon_order_edi_846_log
-- ----------------------------
DROP TABLE IF EXISTS `amazon_order_edi_846_log`;
CREATE TABLE `amazon_order_edi_846_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_account` varchar(10) NOT NULL,
  `path` varchar(100) NOT NULL,
  `create_time` varchar(20) NOT NULL,
  `update_time` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for amazon_order_edi_address_mapping
-- ----------------------------
DROP TABLE IF EXISTS `amazon_order_edi_address_mapping`;
CREATE TABLE `amazon_order_edi_address_mapping` (
  `address_code` varchar(255) NOT NULL,
  `address_code_quantity` varchar(255) DEFAULT NULL,
  `amazon_identifier` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `address1` varchar(255) DEFAULT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `zip` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `time` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`address_code`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for amazon_order_edi_body
-- ----------------------------
DROP TABLE IF EXISTS `amazon_order_edi_body`;
CREATE TABLE `amazon_order_edi_body` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `header_id` int(11) NOT NULL COMMENT 'header id',
  `st_trans_set_id` varchar(3) NOT NULL DEFAULT '' COMMENT 'ST01',
  `st_trans_set_num` varchar(9) NOT NULL DEFAULT '' COMMENT 'ST02',
  `cur_iden` varchar(3) NOT NULL DEFAULT '' COMMENT 'CUR01',
  `cur_currency` varchar(3) NOT NULL DEFAULT '' COMMENT 'CUR02',
  `beg_trans_set_id` varchar(2) NOT NULL DEFAULT '' COMMENT 'BEG01',
  `beg_po_type` varchar(2) NOT NULL DEFAULT '' COMMENT 'BEG02',
  `po_number` varchar(22) NOT NULL DEFAULT '' COMMENT 'BEG03',
  `total_revenue` varchar(10) NOT NULL,
  `beg_po_date` varchar(8) NOT NULL DEFAULT '' COMMENT 'BEG05 CCYYMMDD',
  `ref_id_ia` varchar(30) NOT NULL DEFAULT '' COMMENT 'REF02 IA',
  `ref_id_co` varchar(30) NOT NULL DEFAULT '' COMMENT 'REF02 CO',
  `ref_id_st` varchar(30) NOT NULL DEFAULT '' COMMENT 'REF02 ST',
  `csh_code` varchar(2) NOT NULL DEFAULT '' COMMENT 'CSH01',
  `dtm_quali_063` varchar(8) NOT NULL DEFAULT '' COMMENT 'DTM 063',
  `dtm_quali_064` varchar(8) NOT NULL DEFAULT '' COMMENT 'DTM 064',
  `ctt_lines` varchar(6) NOT NULL DEFAULT '' COMMENT 'CTT01',
  `ctt_hash` varchar(10) NOT NULL DEFAULT '' COMMENT 'CTT02',
  `n1_SF_code` varchar(5) NOT NULL DEFAULT '' COMMENT 'IEA01',
  `n1_ST_name` varchar(20) NOT NULL DEFAULT '' COMMENT 'IEA02',
  `n1_ST_n3` varchar(100) NOT NULL,
  `n1_ST_n4_city` varchar(100) NOT NULL,
  `n1_ST_n4_province` varchar(3) NOT NULL,
  `n1_ST_n4_postal` varchar(10) NOT NULL,
  `n1_ST_n4_country` varchar(4) NOT NULL,
  `td5_shippment_method` varchar(15) NOT NULL,
  `se_segment` varchar(10) NOT NULL DEFAULT '' COMMENT 'SE01',
  `se_trans_set_control` varchar(9) NOT NULL DEFAULT '' COMMENT 'SE02',
  `saved_path` varchar(100) NOT NULL DEFAULT '',
  `user_account` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_number` (`po_number`),
  KEY `header_id` (`header_id`),
  CONSTRAINT `amazon_order_edi_body_ibfk_1` FOREIGN KEY (`header_id`) REFERENCES `amazon_order_edi_header` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9360 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for amazon_order_edi_detail
-- ----------------------------
DROP TABLE IF EXISTS `amazon_order_edi_detail`;
CREATE TABLE `amazon_order_edi_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_id` varchar(22) NOT NULL COMMENT 'BEG03',
  `header_id` int(11) NOT NULL COMMENT 'order edi id',
  `po1_id` varchar(20) NOT NULL DEFAULT '' COMMENT 'PO101',
  `po1_qty` varchar(15) NOT NULL DEFAULT '' COMMENT 'PO102',
  `po1_unit` varchar(2) NOT NULL DEFAULT '' COMMENT 'PO103',
  `po1_unit_price` varchar(17) NOT NULL DEFAULT '' COMMENT 'PO104',
  `po1_basis_unit` varchar(2) NOT NULL DEFAULT '' COMMENT 'PO105',
  `po1_bp_id_quali` varchar(2) NOT NULL DEFAULT '' COMMENT 'PO106',
  `po1_bp_id` varchar(48) NOT NULL DEFAULT '' COMMENT 'PO107',
  `po1_up_id_quali` varchar(2) NOT NULL DEFAULT '' COMMENT 'PO108',
  `po1_up_id` varchar(48) NOT NULL DEFAULT '' COMMENT 'PO109',
  `po1_vp_id_quali` varchar(2) NOT NULL DEFAULT '' COMMENT 'PO114',
  `po1_vp_id` varchar(48) NOT NULL DEFAULT '' COMMENT 'PO115',
  `ctp_price_iden` varchar(3) NOT NULL DEFAULT '' COMMENT 'CTP02',
  `ctp_unit_price` varchar(17) NOT NULL DEFAULT '' COMMENT 'CTP03',
  `po1_section` varchar(500) NOT NULL DEFAULT '' COMMENT 'whole po1 section',
  `ERPSKU` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `header_id` (`header_id`),
  KEY `po_id` (`po_id`),
  CONSTRAINT `amazon_order_edi_detail_ibfk_1` FOREIGN KEY (`header_id`) REFERENCES `amazon_order_edi_header` (`id`),
  CONSTRAINT `amazon_order_edi_detail_ibfk_2` FOREIGN KEY (`po_id`) REFERENCES `amazon_order_edi_body` (`po_number`)
) ENGINE=InnoDB AUTO_INCREMENT=8373 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for amazon_order_edi_error_log
-- ----------------------------
DROP TABLE IF EXISTS `amazon_order_edi_error_log`;
CREATE TABLE `amazon_order_edi_error_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(2) NOT NULL,
  `file_path` varchar(50) NOT NULL COMMENT 'file path',
  `msg` varchar(300) NOT NULL,
  `create_time` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7780 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for amazon_order_edi_header
-- ----------------------------
DROP TABLE IF EXISTS `amazon_order_edi_header`;
CREATE TABLE `amazon_order_edi_header` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `isa_auth_quali` varchar(2) NOT NULL DEFAULT '' COMMENT 'ISA01',
  `isa_auth` varchar(10) NOT NULL DEFAULT '' COMMENT 'ISA02',
  `isa_sec_quali` varchar(2) NOT NULL DEFAULT '' COMMENT 'ISA03',
  `isa_sec` varchar(10) NOT NULL DEFAULT '' COMMENT 'ISA04',
  `isa_interchange_sender_quali` varchar(2) NOT NULL DEFAULT '' COMMENT 'ISA05',
  `isa_interchange_sender_id` varchar(15) NOT NULL DEFAULT '' COMMENT 'ISA06',
  `isa_interchange_receiver_quali` varchar(2) NOT NULL DEFAULT '' COMMENT 'ISA07',
  `isa_interchange_receiver_id` varchar(15) NOT NULL DEFAULT '' COMMENT 'ISA08',
  `isa_interchange_date` varchar(6) NOT NULL DEFAULT '' COMMENT 'ISA09 YYMMDD',
  `isa_interchange_time` varchar(4) NOT NULL DEFAULT '' COMMENT 'ISA10 HHMM',
  `isa_std_iden` varchar(1) NOT NULL DEFAULT '' COMMENT 'ISA11 default u',
  `isa_interchange_ver` varchar(5) NOT NULL DEFAULT '' COMMENT 'ISA12',
  `isa_interchange_num` varchar(9) NOT NULL DEFAULT '' COMMENT 'ISA13',
  `isa_ack_req` varchar(1) NOT NULL DEFAULT '' COMMENT 'ISA14',
  `isa_usage_indi` varchar(1) NOT NULL DEFAULT '' COMMENT 'ISA15',
  `isa_separator` varchar(1) NOT NULL DEFAULT '' COMMENT 'ISA16',
  `gs_funct_id` varchar(2) NOT NULL DEFAULT '' COMMENT 'GS01',
  `gs_app_sender` varchar(15) NOT NULL DEFAULT '' COMMENT 'GS02',
  `gs_app_receiver` varchar(15) NOT NULL DEFAULT '' COMMENT 'GS03',
  `gs_date` varchar(8) NOT NULL DEFAULT '' COMMENT 'GS04 CCYYMMDD',
  `gs_time` varchar(8) NOT NULL DEFAULT '' COMMENT 'GS05',
  `gs_group_control` varchar(9) NOT NULL DEFAULT '' COMMENT 'GS06',
  `gs_res` varchar(2) NOT NULL DEFAULT '' COMMENT 'GS07',
  `gs_ver` varchar(12) NOT NULL DEFAULT '' COMMENT 'GS08',
  `ge_count` varchar(12) NOT NULL DEFAULT '' COMMENT 'GE01',
  `create_time` varchar(30) NOT NULL,
  `user_account` varchar(64) NOT NULL DEFAULT '' COMMENT 'è´¦æˆ·',
  `path` varchar(100) NOT NULL DEFAULT '' COMMENT 'original 850 file path',
  `state` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8718 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for amazon_order_edi_log
-- ----------------------------
DROP TABLE IF EXISTS `amazon_order_edi_log`;
CREATE TABLE `amazon_order_edi_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_account` varchar(20) NOT NULL COMMENT 'amazon account',
  `po_number` varchar(20) NOT NULL COMMENT '850 beg 03',
  `create_time` varchar(20) NOT NULL,
  `state` int(5) NOT NULL COMMENT 'edi.php',
  `update_time` varchar(20) NOT NULL COMMENT 'last update time',
  `note` varchar(20) NOT NULL COMMENT 'note',
  `state_855` int(5) NOT NULL DEFAULT '0',
  `855_GS06` varchar(10) NOT NULL DEFAULT '0',
  `state_856` int(5) NOT NULL DEFAULT '0',
  `856_GS06` varchar(10) NOT NULL DEFAULT '0',
  `order_code` varchar(64) NOT NULL,
  `order_type` varchar(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `po_number` (`po_number`)
) ENGINE=InnoDB AUTO_INCREMENT=8699 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for amazon_order_edi_product
-- ----------------------------
DROP TABLE IF EXISTS `amazon_order_edi_product`;
CREATE TABLE `amazon_order_edi_product` (
  `SKU` varchar(20) NOT NULL,
  `UPC` varchar(20) NOT NULL,
  `ASIN` varchar(20) NOT NULL,
  `title` varchar(20) NOT NULL,
  `warehouse` varchar(20) NOT NULL,
  `warehouse_name` varchar(20) NOT NULL,
  `current_quantity` varchar(20) NOT NULL,
  `target_quantity` varchar(20) NOT NULL,
  `last_update` varchar(20) NOT NULL,
  `user_account` varchar(10) NOT NULL,
  `last_846_id` varchar(10) NOT NULL,
  `is_used` int(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for amazon_order_edi_shipping
-- ----------------------------
DROP TABLE IF EXISTS `amazon_order_edi_shipping`;
CREATE TABLE `amazon_order_edi_shipping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_sm_code` varchar(64) DEFAULT NULL,
  `erp_sm_code` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for amazon_order_edi_store
-- ----------------------------
DROP TABLE IF EXISTS `amazon_order_edi_store`;
CREATE TABLE `amazon_order_edi_store` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store` varchar(20) NOT NULL,
  `user_account` varchar(20) NOT NULL,
  `path` varchar(50) NOT NULL,
  `send_from` varchar(50) NOT NULL,
  `send_to` varchar(50) NOT NULL,
  `vendor_code` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `store_code` (`user_account`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
