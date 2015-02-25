<?php

$installer = $this;
$installer->startSetup();

/**
 * These 2 attributes don't contain any reference to an Avatar class
 * so that if I disable the module, it won't break Magento.
 * 
 * In the same spirit, I don't create the 'used_in_forms' links because
 * if I disable the Avatar module, I don't want to see these attributes in the admin
 * I'll drive their display using observer pattern.
 */
$installer->addAttribute('customer', 'avatar_src', array(
		'label'         	=> 'Avatar Url',
        'type'         		=> 'varchar',
		'input'         	=> 'text',
        'visible'       	=> true,
        'required'      	=> false,
		'position'			=> 1000,
		'sort_order'		=> 1000,
));

$installer->addAttribute('customer', 'avatar_valid', array(
		'label'        		=> 'Avatar validation',
        'type'          	=> 'int',
        'input'         	=> 'select',
        'visible'       	=> true,
		'default'			=> 0,
        'required'      	=> false,
		'position'			=> 999,
		'sort_order'		=> 999,
));

if (version_compare(Mage::getVersion(), '1.6.0', '<='))
{
      $customer = Mage::getModel('customer/customer');
      $attrSetId = $customer->getResource()->getEntityType()->getDefaultAttributeSetId();
      $setup->addAttributeToSet('customer', $attrSetId, 'General', 'avatar_src');
      $setup->addAttributeToSet('customer', $attrSetId, 'General', 'avatar_valid');
}

$installer->endSetup();