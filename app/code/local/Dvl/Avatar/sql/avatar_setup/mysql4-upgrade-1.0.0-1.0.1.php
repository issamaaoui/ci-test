<?php

$installer = $this;
$installer->startSetup();

$installer->addAttribute('customer', 'avatar_fullpath', array(
    'label'         	=> 'Avatar Full Path',
    'type'         		=> 'varchar',
    'input'         	=> 'text',
    'visible'       	=> false,
    'required'      	=> false,
    'position'			=> 1001,
    'sort_order'		=> 1001,
));

$installer->endSetup();