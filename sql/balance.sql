DROP TABLE IF EXISTS `balance`;
CREATE TABLE `balance` (account int unsigned PRIMARY KEY, balance int NOT NULL DEFAULT 0) ENGINE=innodb;
DROP TABLE IF EXISTS `CharAuctionhouse`;
CREATE TABLE `CharAuctionhouse` (guid int unsigned PRIMARY KEY, owner int unsigned NOT NULL, startbid int unsigned NOT NULL, buyout int unsigned NOT NULL DEFAULT 0, lastbid int unsigned NOT NULL DEFAULT 0, bidder int unsigned NOT NULL DEFAULT 0, created TIMESTAMP, expires TIMESTAMP) ENGINE=innodb;
