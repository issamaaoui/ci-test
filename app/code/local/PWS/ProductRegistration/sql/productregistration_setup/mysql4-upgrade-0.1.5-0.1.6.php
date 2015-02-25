<?php
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('customer_product_registrations')} ADD `is_valid` char(3) NOT NULL DEFAULT 'yes' AFTER `purchased_from`;
");

$installer->endSetup();
