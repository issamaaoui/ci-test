<?php
$installer = $this;
$installer->startSetup();

$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');

$salesTable = $resource->getTableName('sales/order');

$sapOrderTable = $resource->getTableName('dvl_sapbyd/order');

$query = "UPDATE ".$salesTable." sfo, ".$sapOrderTable." sapo 
	SET sfo.sapbyd_order_id = sapo.sapbyd_order_id, 
	sfo.sapbyd_customer_id = sapo.sapbyd_customer_id, 
	sfo.sapbyd_code = sapo.code,
	sfo.sapbyd_message = sapo.message
	WHERE sfo.entity_id = sapo.order_id";


$writeConnection->query($query);

$installer->endSetup();