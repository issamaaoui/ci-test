<?php
class Fidesio_Preorder_Block_Adminhtml_Code extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_code';
    $this->_blockGroup = 'preorder';
    $this->_headerText = Mage::helper('preorder')->__('Manage shopping codes');
    $this->_addButtonLabel = Mage::helper('preorder')->__('New code');
    parent::__construct();
  }
}