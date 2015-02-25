<?php

$installer = $this;
$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('dvl_sapbyd_log')};
    CREATE TABLE {$this->getTable('dvl_sapbyd_log')} (
        `id` INT NOT NULL AUTO_INCREMENT,
        `dvl_sapbyd_customer_id` INT NOT NULL,
        `dvl_sapbyd_order_id` INT NOT NULL,
        `type` varchar(50) NOT NULL default '',
        `status` varchar(50) NOT NULL default 'OK',
        `response` TEXT,
        `message` TEXT,
        `created_on` datetime,
        `updated_on` datetime,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('dvl_sapbyd_customer')};
CREATE TABLE {$this->getTable('dvl_sapbyd_customer')} (
      `id` INT NOT NULL AUTO_INCREMENT,
      `customer_id` INT NOT NULL,
      `sapbyd_customer_id` varchar(255) NOT NULL default '',
      `code` varchar(50) NOT NULL default '',
      `message` TEXT,    
      `request` TEXT,   
      `created_on` datetime,
      `updated_on` datetime,
      PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('dvl_sapbyd_order')};
    CREATE TABLE {$this->getTable('dvl_sapbyd_order')} (
        `id` INT NOT NULL AUTO_INCREMENT,
        `order_id` INT NOT NULL,
        `sapbyd_order_id` varchar(255) NOT NULL default '',
        `customer_id` INT NOT NULL,
        `sapbyd_customer_id` varchar(255) NOT NULL default '',
        `code` varchar(50) NOT NULL default '',
        `message` TEXT,    
        `request` TEXT, 
        `created_on` datetime,
        `updated_on` datetime,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('dvl_sapbyd_invoice')};
    CREATE TABLE {$this->getTable('dvl_sapbyd_invoice')} (
        `id` INT NOT NULL AUTO_INCREMENT,
        `order_id` INT NOT NULL,
        `sapbyd_order_id` varchar(255) NOT NULL default '',
        `invoice_id` INT NOT NULL,
        `sapbyd_invoice_id` varchar(255) NOT NULL default '',
        `customer_id` INT NOT NULL,
        `sapbyd_customer_id` varchar(255) NOT NULL default '',
        `code` varchar(50) NOT NULL default '',
        `message` TEXT,
        `request` TEXT,
        `created_on` datetime,
        `updated_on` datetime,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
