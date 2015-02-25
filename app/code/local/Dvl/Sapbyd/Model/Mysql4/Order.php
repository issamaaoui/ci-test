<?php
 
class Dvl_Sapbyd_Model_Mysql4_Order extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('dvl_sapbyd/order', 'id');
    }
    
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getCreatedOn()) {
            $object->setCreatedOn($this->formatDate(time()));
        }

        $object->setUpdatedOn($this->formatDate(time()));
        parent::_beforeSave($object);
    }
}