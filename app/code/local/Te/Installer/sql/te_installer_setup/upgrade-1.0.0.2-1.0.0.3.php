<?php

$installer = $this;
$installer->startSetup();

$sapCustomers = Mage::getResourceModel("dvl_sapbyd/customer_collection")->load();

foreach ($sapCustomers as $sapCustomer) {
	$customer = mage::getModel("customer/customer")->load($sapCustomer->getCustomerId());
	
	if(!$customer->getId())
		continue;

	$customer->setSapbydCustomerId($sapCustomer->getSapbydCustomerId());
	$customer->setSapbydCode($sapCustomer->getCode());
	$customer->setSapbydMessage($sapCustomer->getMessage());
	$customer->save();
}


$installer->endSetup();