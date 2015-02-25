<?php
 
class Dvl_Sapbyd_Model_Mysql4_Log extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('dvl_sapbyd/log', 'id');
    }
}