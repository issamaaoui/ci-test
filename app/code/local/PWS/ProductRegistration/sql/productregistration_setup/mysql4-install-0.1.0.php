<?php

$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute('catalog_product', 'is_registrable', array(
    'group'    		=> 'General',
    'label'    		=> 'Is Registrable',
    'type'			=> 'int',
    'required'      => 1,
    'type'          => 'int',
    'backend'       => '',
    'frontend'      => '',
    'input'         => 'select',
    'class'         => '',
    'source'        => 'eav/entity_attribute_source_boolean',
    'visible'       => 1,
    'user_defined'  => 1,
    'is_user_defined' => true,
));

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('customer_product_registrations')};
CREATE TABLE {$this->getTable('customer_product_registrations')} (
  `registration_id` int(11) unsigned NOT NULL auto_increment,
  `customer_id` int(11) unsigned NOT NULL default 0,
  `product_id` int(11) unsigned NOT NULL default 0,
  `serial_number` varchar(255) NOT NULL default '',
  `date_of_purchase` date NOT NULL default '0000-00-00',
  `purchased_from` varchar(255) NOT NULL default '',
  PRIMARY KEY (`registration_id`),
  KEY (`customer_id`),
  KEY (`product_id`),
  CONSTRAINT FOREIGN KEY (`product_id`) REFERENCES {$this->getTable('catalog_product_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT FOREIGN KEY (`customer_id`) REFERENCES {$this->getTable('customer_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
