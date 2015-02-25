<?php
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('customer_product_registrations')} ADD `notes` text AFTER `purchased_from`;
");

$installer->endSetup();
