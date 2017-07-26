<?php
class Installer extends CComponent
{	
	public static function install($isInstallMode)
	{
		if (!$isInstallMode)
			throw new CException("Please, set or uncomment module option \"'installMode' => true\" in config file.");
		
		$isConsoleApp = get_class(Yii::app()) == "CConsoleApplication";
		$db = Yii::app()->db;
		$prefix = $db->tablePrefix;
		
		$sql = "drop table if exists {$prefix}yiiscraper_related_data, {$prefix}yiiscraper_link, {$prefix}yiiscraper_log";
		$db->createCommand($sql)->execute();
	
		$sql = "CREATE TABLE `{$prefix}yiiscraper_link` (
				`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`url` varchar(255) NOT NULL,
				`depth` int(11) unsigned NOT NULL DEFAULT '0',
				`active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'flag, whether link is not fecthed yet. If fetched, active is 0',
				`content` mediumtext,
				PRIMARY KEY (`id`),
				UNIQUE KEY `url` (`url`),
				KEY `active` (`active`),
				KEY `depth` (`depth`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='List of parsed links in documents, fetched with scraper'";
		$db->createCommand($sql)->execute();
		Yii::trace(Yii::t('YiiScraper', "Table '{table}' was created", array(
			'{table}' => "{$prefix}yiiscraper_link",
		)));
			
		$sql = "CREATE TABLE `{$prefix}yiiscraper_log` (
				`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`created` datetime NOT NULL COMMENT 'start time',
				`bytes_received` int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'how many bytes were received during one scraper run',
				`documents_received` int(11) unsigned NOT NULL DEFAULT 0,
				`html_documents_received` int(11) unsigned NOT NULL DEFAULT 0,
				`created_links_count` int(11) unsigned NOT NULL DEFAULT 0,
				`updated_documents_count` int(11) unsigned NOT NULL DEFAULT 0,
				`duration` int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'work duration in seconds',
				`result` varchar(250) NOT NULL,
				`details` text COMMENT 'stores some service information',
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Reports about scraper work'";
		$db->createCommand($sql)->execute();
		Yii::trace(Yii::t('YiiScraper', "Table '{table}' was created", array(
			'{table}' => "{$prefix}yiiscraper_log",
		)));
		
		$sql = "CREATE TABLE `{$prefix}yiiscraper_related_data` (
				`link_id_from` int(11) unsigned NOT NULL,
				`link_id_to` int(11) unsigned NOT NULL,
				PRIMARY KEY (`link_id_from`,`link_id_to`),
				UNIQUE KEY `link_id_to` (`link_id_to`,`link_id_from`),
				CONSTRAINT `tbl_yiiscraper_related_data_ibfk_1` FOREIGN KEY (`link_id_from`) REFERENCES `tbl_yiiscraper_link` (`id`) ON DELETE CASCADE,
				CONSTRAINT `tbl_yiiscraper_related_data_ibfk_2` FOREIGN KEY (`link_id_to`) REFERENCES `tbl_yiiscraper_link` (`id`) ON DELETE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Table to handle relations of links'";
		$db->createCommand($sql)->execute();
		Yii::trace(Yii::t('YiiScraper', "Table '{table}' was created", array(
			'{table}' => "{$prefix}yiiscraper_related_data",
		)));
		
		return true;
	}
	
	public static function uninstall($isInstallMode)
	{
		if (!$isInstallMode)
			throw new CException("Please, set or uncomment module option \"'installMode' => true\" in config file.");
		
		$db = Yii::app()->db;
		$prefix = $db->tablePrefix;
		
		$sql = "drop table {$prefix}yiiscraper_related_data";
		$db->createCommand($sql)->execute();
		Yii::trace(Yii::t('YiiScraper', "Table '{table}' was dropped", array(
			'{table}' => "{$prefix}yiiscraper_related_data",
		)));
		
		$sql = "drop table {$prefix}yiiscraper_log";
		$db->createCommand($sql)->execute();
		Yii::trace(Yii::t('YiiScraper', "Table '{table}' was dropped", array(
			'{table}' => "{$prefix}yiiscraper_log",
		)));
		
		$sql = "drop table {$prefix}yiiscraper_link";
		$db->createCommand($sql)->execute();
		Yii::trace(Yii::t('YiiScraper', "Table '{table}' was dropped", array(
			'{table}' => "{$prefix}yiiscraper_link",
		)));
		
		return true;
	}
}
?>