<?php
class Fidesio_Fluidmenu_Block_Adminhtml_Fluidmenu_Items extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_fluidmenu_items';
    $this->_blockGroup = 'fluidmenu';
    $this->_headerText = Mage::helper('fluidmenu')->__('Gestion des liens de menu');
    $this->_addButtonLabel = Mage::helper('fluidmenu')->__('Nouveau item menu');
    parent::__construct();
  }
}