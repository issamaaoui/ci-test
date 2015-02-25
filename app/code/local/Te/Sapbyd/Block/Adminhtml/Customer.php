<?php

/**
 * @author te
 *
 */
class Te_Sapbyd_Block_Adminhtml_Customer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'te_sapbyd';
        $this->_controller = 'adminhtml_customer';
        $this->_headerText = Mage::helper('customer')->__('Manage Customers');

        parent::__construct();
        $this->_removeButton('add');
    }
}
