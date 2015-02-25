<?php
class Fidesio_Fluidmenu_Block_Adminhtml_Fluidmenu_Items_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'fluidmenu_item_id';
        $this->_blockGroup = 'fluidmenu';
        $this->_controller = 'adminhtml_fluidmenu_items';
        
        $this->_updateButton('save', 'label', Mage::helper('fluidmenu')->__('Sauvegarder'));
        $this->_updateButton('delete', 'label', Mage::helper('fluidmenu')->__('Supprimer'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__("Sauvegarder et continuer l'édition"),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('fluidmenu_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'fluidmenu_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'fluidmenu_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('fluidmenu_data') && Mage::registry('fluidmenu_data')->getId() ) {
            return Mage::helper('fluidmenu')->__("Lien Menu: '%s'", $this->htmlEscape(Mage::registry('fluidmenu_data')->getName()));
        } else {
            return Mage::helper('fluidmenu')->__('Nouveau lien Menu');
        }
    }
}