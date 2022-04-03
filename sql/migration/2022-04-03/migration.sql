DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `password` varchar(150) NOT NULL,
  `date` datetime DEFAULT now(),
  `nume` varchar(100) NOT NULL,
  `uri` varchar(100) NOT NULL,
  `telefon` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `image` bigint DEFAULT NULL,
  `firebase` text CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `status` bigint DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `users`
  ADD KEY `date` (`date`),
  ADD KEY `email` (`email`),
  ADD KEY `password` (`password`),
  ADD KEY `uri` (`uri`);

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `date`, `nume`, `uri`, `telefon`, `image`, `firebase`, `status`) VALUES
(6, 'admin@admin.com', '21232f297a57a5a743894a0e4a801fc3', '2022-04-03 12:38:59', '', '', '', 0, '', 1),
(49, 'e9', 'd9a9d61ef9ac1fb462fb3ce61f509700', '2018-09-22 10:52:07', 'n8', 'n8', 't9', 0, '', 0),
(61, 'onoffrei@gmail.com', 'acbd18db4cc2f85cedef654fccc4a4d8', '2022-01-02 13:48:36', 'onoffrei', 'onoffrei', NULL, NULL, NULL, 0);


DROP TABLE IF EXISTS `spitale_users`;
CREATE TABLE `spitale_users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `spital` bigint UNSIGNED NOT NULL,
  `user` bigint UNSIGNED NOT NULL,
  `level` tinyint UNSIGNED NOT NULL,
  `descriere` text,
  `autoplanificare` tinyint UNSIGNED,
  `cronstart` datetime,
  `cronnext` datetime,
  `croncontinue` datetime,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

ALTER TABLE `spitale_users`
  ADD KEY `spital` (`spital`),
  ADD KEY `user` (`user`);

DROP TABLE IF EXISTS `spitale_images`;
CREATE TABLE `spitale_images` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `spital` bigint UNSIGNED NOT NULL,
  `image` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

ALTER TABLE `spitale_images`
  ADD KEY `spital` (`spital`),
  ADD KEY `image` (`image`);

DROP TABLE IF EXISTS `images`;
CREATE TABLE `images` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` text,
  `path` text,
  `owner` bigint UNSIGNED NOT NULL,
  `date` datetime,
  `w` bigint,
  `h` bigint,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

ALTER TABLE `images`
  ADD KEY `owner` (`owner`);

  
DROP TABLE IF EXISTS `mesaje`;
CREATE TABLE `mesaje` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `from` bigint UNSIGNED NOT NULL,
  `to` bigint UNSIGNED NOT NULL,
  `text` text,
  `date` datetime,
  `isread` tinyint,
  `browser` bigint UNSIGNED,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

ALTER TABLE `mesaje`
  ADD KEY `from` (`from`),
  ADD KEY `to` (`to`),
  ADD KEY `date` (`date`)
  ;

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `browser` bigint UNSIGNED NOT NULL,
  `user` bigint UNSIGNED NOT NULL,
  `alttype` bigint UNSIGNED,
  `altid` bigint UNSIGNED ,
  `rate` text ,
  `text` text,
  `date` datetime,
  `email` text,
  `nume` text,
  `parent` bigint UNSIGNED,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

DROP TABLE IF EXISTS `programari`;
CREATE TABLE `programari` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nume` varchar(150) ,
  `start` datetime NOT NULL,
  `stop` datetime NOT NULL,
  `doctor` bigint UNSIGNED NOT NULL,
  `specializare` bigint UNSIGNED NOT NULL,
  `spital` bigint NOT NULL,
  `user` bigint NOT NULL,
  `observatii` text,
  `isread` bigint,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
ALTER TABLE `programari`
  ADD KEY `specializare` (`specializare`),
  ADD KEY `start` (`start`),
  ADD KEY `stop` (`stop`),
  ADD KEY `doctor` (`doctor`);


