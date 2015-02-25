<?php

$installer = $this;
$installer->startSetup();

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

$installer->endSetup();