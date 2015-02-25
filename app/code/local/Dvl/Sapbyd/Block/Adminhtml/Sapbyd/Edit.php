<?php
 
class Dvl_Sapbyd_Block_Adminhtml_Sapbyd_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
               
        $this->_objectId = 'id';
        $this->_blockGroup = 'sapbyd';
        $this->_controller = 'adminhtml_sapbyd';
 
        $this->_updateButton('save', 'label', Mage::helper('sapbyd')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('sapbyd')->__('Delete Item'));
        
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);
        
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('sapbyd_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'sapbyd_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'sapbyd_content');
                }
            }
        
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";        
        
    }
 
    public function getHeaderText()
    {
        if( Mage::registry('sapbyd_data') && Mage::registry('sapbyd_data')->getId() ) {
            return Mage::helper('sapbyd')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('sapbyd_data')->getTitle()));
        } else {
            return Mage::helper('sapbyd')->__('Add Item');
        }
    }
}