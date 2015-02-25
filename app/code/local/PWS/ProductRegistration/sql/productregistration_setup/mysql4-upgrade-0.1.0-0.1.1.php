<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('customer_product_registrations')} ADD `created_on` datetime;
ALTER TABLE {$this->getTable('customer_product_registrations')} ADD `updated_on` datetime;
");

$installer->endSetup();

