# Host: localhost  (Version: 5.5.53)
# Date: 2019-02-14 19:05:57
# Generator: MySQL-Front 5.3  (Build 4.234)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "order"
#

CREATE TABLE `order` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trade_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `patment_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` int(11) NOT NULL,
  `completed_at` int(11) NOT NULL,
  `failed_at` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `callback_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `callback_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `callback_next_retry` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `channel_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `order_order_id_unique` (`order_id`) USING BTREE,
  KEY `order_user_id_index` (`user_id`) USING BTREE,
  KEY `order_trade_id_index` (`trade_id`) USING BTREE,
  KEY `order_status_index` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=734 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;
