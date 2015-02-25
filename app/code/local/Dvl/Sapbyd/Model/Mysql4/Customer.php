<?php
 
class Dvl_Sapbyd_Model_Mysql4_Customer extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('dvl_sapbyd/customer', 'id');
    }
}