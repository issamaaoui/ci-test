<?php
class Fidesio_Fluidmenu_Model_Mysql4_Fluidmenu_Items extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('fluidmenu/fluidmenu_items', 'fluidmenu_item_id');
    }
}