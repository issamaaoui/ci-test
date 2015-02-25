<?php
class Fidesio_Preorder_Block_Adminhtml_Code_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('code_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('preorder')->__('Informations du code'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('preorder')->__('Information du code'),
          'title'     => Mage::helper('preorder')->__('Information du code'),
          'content'   => $this->getLayout()->createBlock('preorder/adminhtml_code_edit_tab_general')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}