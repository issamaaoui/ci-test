<?php
class Fidesio_Fluidmenu_Block_Adminhtml_Fluidmenu extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_fluidmenu';
    $this->_blockGroup = 'fluidmenu';
    $this->_headerText = Mage::helper('fluidmenu')->__('Gestion des menus');
    $this->_addButtonLabel = Mage::helper('fluidmenu')->__('Nouveau menu');
    parent::__construct();
  }
}