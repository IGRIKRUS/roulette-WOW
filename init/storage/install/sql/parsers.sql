CREATE TABLE IF NOT EXISTS `parsers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acc_id` int(11) DEFAULT NULL,
  `acc_name` varchar(255) DEFAULT NULL,
  `status` int(2) DEFAULT '0',
  `name_top` varchar(255) DEFAULT NULL,
  `vote_id` int(30) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `date_add` datetime DEFAULT NULL,
  `ip` varchar(30) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `vp` int(2) DEFAULT NULL,
  `hash` varchar(34) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;