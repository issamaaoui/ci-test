<?php

/**
 * @author te
 *
 */
class Te_Sapbyd_Block_Adminhtml_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'te_sapbyd';
        $this->_controller = 'adminhtml_order';
        $this->_headerText = Mage::helper('sales')->__('Orders');

        parent::__construct();
        $this->_removeButton('add');
    }
}
