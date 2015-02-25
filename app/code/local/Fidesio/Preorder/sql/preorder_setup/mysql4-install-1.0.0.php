<?php

$installer = $this;
$installer->startSetup();

try{
    // Create table 'fd_preorder_code'
	$installer->run("
		DROP TABLE IF EXISTS {$this->getTable('fd_preorder_code')};
		CREATE TABLE {$this->getTable('fd_preorder_code')} (
		  	`auto_id` int(11) unsigned NOT NULL auto_increment,
		  	`code` varchar(50) NOT NULL default '',
		  	`customer_id` int(11) unsigned,
		  	`order_id` int(11) unsigned,
		  	`status` int(2) NOT NULL default 1,
		  	`created_time` datetime,
            `updated_time` datetime,
		  	PRIMARY KEY (`auto_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	    ALTER TABLE {$this->getTable('fd_preorder_code')} ADD UNIQUE unique_code (`code`);
	");
	
}catch(Exception $e){}


$installer->endSetup();
