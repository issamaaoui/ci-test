<?php
 
class Dvl_Sapbyd_Block_Adminhtml_Sapbyd_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
 
    public function __construct()
    {
        parent::__construct();
        $this->setId('sapbyd_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('sapbyd')->__('News Information'));
    }
 
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('sapbyd')->__('Item Information'),
            'title'     => Mage::helper('sapbyd')->__('Item Information'),
            'content'   => $this->getLayout()->createBlock('sapbyd/adminhtml_sapbyd_edit_tab_form')->toHtml(),
        ));
       
        return parent::_beforeToHtml();
    }
}