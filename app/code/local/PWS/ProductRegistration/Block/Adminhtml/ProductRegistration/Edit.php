<?php
class PWS_ProductRegistration_Block_Adminhtml_ProductRegistration_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_controller = 'rating';//yeah, a little lazy; I should have created a new class ...Edit_Form

        $this->_updateButton('save', 'label', Mage::helper('pws_productregistration')->__('Save Product Registration'));

        if( $this->getRequest()->getParam($this->_objectId)) {
            $this->_updateButton('delete', 'label', Mage::helper('pws_productregistration')->__('Delete Product Registration'));

            $productRegistrationData = Mage::getModel('pws_productregistration/productregistration')
                ->load($this->getRequest()->getParam($this->_objectId));

            Mage::register('product_registation_data', $productRegistrationData);
        }

    }

    public function getHeaderText()
    {
        if( Mage::registry('product_registration') && Mage::registry('product_registration')->getId() ) {
            return Mage::helper('pws_productregistration')->__("Edit Product Registration", $this->htmlEscape(Mage::registry('product_registration')->getName()));
        } else {
            return Mage::helper('pws_productregistration')->__("Add Product Registration");
        }
    }
}
