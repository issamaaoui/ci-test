<?php
 
class Dvl_Sapbyd_Block_Adminhtml_Sapbyd extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_sapbyd';
        $this->_blockGroup = 'sapbyd';
        $this->_headerText = Mage::helper('sapbyd')->__('Item Manager');
        $this->_addButtonLabel = Mage::helper('sapbyd')->__('Add Item');
        parent::__construct();
    }
}