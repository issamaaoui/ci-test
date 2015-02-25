<?php
class Fidesio_Preorder_Model_Mysql4_Code extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('preorder/code', 'auto_id');
    }
}