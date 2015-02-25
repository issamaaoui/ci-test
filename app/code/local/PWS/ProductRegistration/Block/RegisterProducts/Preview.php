<?php
class PWS_ProductRegistration_Block_RegisterProducts_Preview extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('pws_productregistration/registerproducts/preview.phtml');
        
    }
   
    
    public function getRegistrationData()
    {
    	return Mage::registry('registration_data');	
    }
    
   
}
