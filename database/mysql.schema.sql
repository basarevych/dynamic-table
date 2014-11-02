DROP TABLE IF EXISTS `sample`;

CREATE TABLE `sample` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `value_string` varchar(255) NULL,
    `value_integer` int NULL,
    `value_float` float NULL,
    `value_boolean` tinyint(1) NULL,
    `value_datetime` datetime NULL,
    CONSTRAINT `sample_pk` PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
