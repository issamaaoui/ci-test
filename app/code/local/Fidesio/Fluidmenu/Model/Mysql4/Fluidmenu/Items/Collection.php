<?php
class Fidesio_Fluidmenu_Model_Mysql4_Fluidmenu_Items_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('fluidmenu/fluidmenu_items');
    }
}