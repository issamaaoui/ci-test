<?php

class Fidesio_Preorder_Block_Adminhtml_Code_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId    = 'id';
        $this->_blockGroup  = 'preorder';
        $this->_controller  = 'adminhtml_code';

        $this->_updateButton('save', 'label', Mage::helper('adminhtml')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('adminhtml')->__('Delete'));

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('code_data') && Mage::registry('code_data')->getId() ) {
            return Mage::helper('preorder')->__("Code : '%s'", $this->escapeHtml(Mage::registry('code_data')->getCode()));
        } else {
            return Mage::helper('preorder')->__('New code');
        }

    }
}