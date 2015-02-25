<?php
class PWS_ProductRegistration_Block_RegisterProducts_Email extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('pws_productregistration/registerproducts/email.phtml');
    }


    public function showAccountInfo()
    {
    	return Mage::registry('registration_data');
    }

    public function getRegisteredData()
    {
    	return $this->getData('registered_data');
    }


}
