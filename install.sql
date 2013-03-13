CREATE TABLE `prr_suomenpankit` (
 `id` int(5) unsigned NOT NULL auto_increment,
 `orders_id` int(11) NOT NULL default '0',
 `referid` bigint(20) NOT NULL default '0',
 `session_id` varchar(32) default NULL,
 PRIMARY KEY  (`id`)
);
