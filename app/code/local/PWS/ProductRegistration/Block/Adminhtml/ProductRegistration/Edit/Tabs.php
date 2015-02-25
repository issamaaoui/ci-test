<?php
class PWS_ProductRegistration_Block_Adminhtml_ProductRegistration_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('productregistration_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('pws_productregistration')->__('Product Registration Information'));
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('pws_productregistration')->__('Product Registration Information'),
            'title'     => Mage::helper('pws_productregistration')->__('Product Registration Information'),
            'content'   => $this->getLayout()->createBlock('pws_productregistration/adminhtml_productRegistration_edit_tab_form')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}
