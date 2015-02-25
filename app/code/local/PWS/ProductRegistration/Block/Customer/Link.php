<?php
class PWS_ProductRegistration_Block_Customer_Link extends Mage_Core_Block_Template
{
    public function addLink($name, $path, $label, $urlParams = array())
    {
        $label = Mage::helper('pws_productregistration')->getRegisteredProductsTitle();

        return $this->getLayout()->getBlock('customer_account_navigation')->addLink($name, $path, $label, $urlParams);
    }
}
