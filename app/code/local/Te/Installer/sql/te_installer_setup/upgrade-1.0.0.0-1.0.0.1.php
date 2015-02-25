<?php 

$installer = $this;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);


$installer->removeAttribute("customer", "sapbyd_customer_id");
$installer->addAttribute("customer", "sapbyd_customer_id",  array(
    "type"     => "varchar",
    "backend"  => "",
    "label"    => "SAP Customer Id",
    "input"    => "text",
    "source"   => "",
    "visible"  => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique"     => false,
    "note"       => "SAP Customer Id"

 ));

$installer->removeAttribute("customer", "sapbyd_code");

$installer->addAttribute("customer", "sapbyd_code",  array(
    "type"     => "varchar",
    "backend"  => "",
    "label"    => "SAP Customer Code",
    "input"    => "text",
    "source"   => "",
    "visible"  => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique"     => false,
    "note"       => "SAP Customer Code"

 ));
$installer->removeAttribute("customer", "sap_message");
$installer->removeAttribute("customer", "sapbyd_message");
$installer->addAttribute("customer", "sapbyd_message",  array(
    "type"     => "varchar",
    "backend"  => "",
    "label"    => "SAP Customer Message",
    "input"    => "text",
    "source"   => "",
    "visible"  => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique"     => false,
    "note"       => "SAP Customer Message"

 ));


$installer->endSetup();