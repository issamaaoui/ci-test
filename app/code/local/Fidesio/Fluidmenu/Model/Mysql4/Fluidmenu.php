<?php
class Fidesio_Fluidmenu_Model_Mysql4_Fluidmenu extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('fluidmenu/fluidmenu', 'fluidmenu_id');
    }
    
    
    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        $condition = array(
            'fluidmenu_id = ?' => (int) $object->getId(),
        );
        $this->_getWriteAdapter()->delete($this->getTable('fluidmenu_store'), $condition);

        return parent::_beforeDelete($object);
    }
    

    
  	protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $condition = $this->_getWriteAdapter()->quoteInto('fluidmenu_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('fluidmenu_store'), $condition);

        if (count($object->getData('stores')) && (!in_array(0, (array)$object->getData('stores')))) {
            foreach ((array)$object->getData('stores') as $store) {
                $data = array();
                $data['fluidmenu_id'] = $object->getId();
                $data['store_id'] = $store;
                $this->_getWriteAdapter()->insert($this->getTable('fluidmenu_store'), $data);
            }
        } else {
            $data = array();
            $data['fluidmenu_id'] = $object->getId();
            $data['store_id'] = '0';
            $this->_getWriteAdapter()->insert($this->getTable('fluidmenu_store'), $data);
        }
        
        return parent::_afterSave($object);
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('fluidmenu_store'))
            ->where('fluidmenu_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $stores = array();
            foreach ($data as $row) {
                $stores[] = $row['store_id'];
            }
            $object->setData('store_id', $stores);
        }

        return parent::_afterLoad($object);
    }
    
    
}