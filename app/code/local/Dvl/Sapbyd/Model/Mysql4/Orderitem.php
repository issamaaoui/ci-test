<?php
 
class Dvl_Sapbyd_Model_Mysql4_Orderitem extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('dvl_sapbyd/orderitem', 'id');
    }
}