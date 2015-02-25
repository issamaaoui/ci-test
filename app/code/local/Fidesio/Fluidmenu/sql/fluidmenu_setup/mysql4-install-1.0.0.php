<?php

$installer = $this;
$installer->startSetup();

try{
	// Create table 'fd_fluidmenu'
	$installer->run("
		DROP TABLE IF EXISTS {$this->getTable('fd_fluidmenu')};
		CREATE TABLE {$this->getTable('fd_fluidmenu')} (
		  	`fluidmenu_id` int(11) unsigned NOT NULL auto_increment,
		  	`name` varchar(255) NOT NULL default '',
		  	`key_menu` varchar(255) NOT NULL default '',
		  	`status` int(2) NOT NULL default 1,
		  	`description` text NOT NULL DEFAULT '',
		  	PRIMARY KEY (`fluidmenu_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	");
	
	// Create table 'fd_fluidmenu_item'
	$installer->run("
		DROP TABLE IF EXISTS {$this->getTable('fd_fluidmenu_item')};
		CREATE TABLE {$this->getTable('fd_fluidmenu_item')} (
		  	`fluidmenu_item_id` int(11) unsigned NOT NULL auto_increment,
		  	`name` varchar(255) NOT NULL default '',
		  	`menu_id` int(11) NOT NULL default 0,
		  	`parent_id` int(11) NOT NULL default 0,
		  	`url` varchar(255) default '',
		  	`cms_type` varchar(255) default '',
		  	`ancre` varchar(255) default '',
		  	`id_link` varchar(255) default '',
		  	`class_link` varchar(255) default '',
		  	`title_link` varchar(255) default '',
		  	`target_link` varchar(255) default '',
		  	`level` int(3) default 1,	
		  	`position` int(3) default 0,		  		  	
		  	`status` int(2) NOT NULL  default 1,
		  	PRIMARY KEY (`fluidmenu_item_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	");	
	
	// Create table 'fd_fluidmenu_store'
	$installer->run("
		DROP TABLE IF EXISTS {$this->getTable('fd_fluidmenu_store')};
		CREATE TABLE {$this->getTable('fd_fluidmenu_store')} (
	    	`fluidmenu_id` smallint(6) unsigned,
	    	`store_id` smallint(6) unsigned
	) ENGINE = InnoDB DEFAULT CHARSET = utf8;
	");
	
}catch(Exception $e){}


$installer->endSetup();
