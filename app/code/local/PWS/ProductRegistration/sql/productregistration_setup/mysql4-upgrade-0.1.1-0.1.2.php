<?php
$installer = $this;
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('product_registrations_serial_numbers')};
CREATE TABLE {$this->getTable('product_registrations_serial_numbers')} (
  `id` int(11) unsigned not null auto_increment,
  `sku` varchar(50),
  `valid_serial_number` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();

