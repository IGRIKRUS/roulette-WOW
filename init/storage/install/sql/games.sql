CREATE TABLE IF NOT EXISTS `games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_acc` int(11) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `macros` text,
  `item_icon` varchar(255) NOT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `item_tooltip` text,
  `price` int(11) NOT NULL,
  `status` int(11) DEFAULT '0',
  `id_char` int(11) DEFAULT NULL,
  `name_char` varchar(255) NOT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `date_send` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;