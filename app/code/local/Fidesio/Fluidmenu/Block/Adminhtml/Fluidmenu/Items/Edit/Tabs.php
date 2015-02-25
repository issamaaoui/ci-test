<?php
class Fidesio_Fluidmenu_Block_Adminhtml_Fluidmenu_Items_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('fluidmenu_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('fluidmenu')->__('Lien de menu Information'));
  }

  protected function _beforeToHtml()
  {
  	
  	   $this->addTab('info', array(
            'label'     => Mage::helper('fluidmenu')->__('Informations'),
            'content'   => $this->getLayout()->createBlock('fluidmenu/adminhtml_fluidmenu_items_edit_tab_form')->toHtml(),
        ));
  	
      return parent::_beforeToHtml();
  }
  
}