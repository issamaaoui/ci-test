<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'sapbyd_order_id', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'nullable'  => true,
        'comment'   => 'SAP Order Id'
    ));
    
$installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'sapbyd_customer_id', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'nullable'  => true,
        'comment'   => 'SAP Customer Id'
    ));

    $installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'sapbyd_code', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 50,
        'nullable'  => true,
        'comment'   => 'SAP Code'
    ));
    

    $installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'sapbyd_message', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable'  => true,
        'comment'   => 'SAP Code'
    ));
    
$installer->endSetup();