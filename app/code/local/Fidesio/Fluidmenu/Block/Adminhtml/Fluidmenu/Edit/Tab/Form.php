<?php

class Fidesio_Fluidmenu_Block_Adminhtml_Fluidmenu_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

  protected function _prepareForm() {
    $form = new Varien_Data_Form();
    $this->setForm($form);
    $fieldset = $form->addFieldset('fluidmenu_form', array('legend' => Mage::helper('fluidmenu')->__('Information du menu')));

    // Nom
    $fieldset->addField('name', 'text', array(
        'label' => Mage::helper('fluidmenu')->__('Name'),
        'required' => true,
        'name' => 'name',
    ));

    // Identifiant menu
     $fieldset->addField('key_menu', 'text', array(
        'label'     => Mage::helper('fluidmenu')->__('Identifiant'),
        'title'     => Mage::helper('fluidmenu')->__('Identifiant menu'),
        'required'  => true,
        'name'      => 'key_menu',
        'class'     => 'validate-xml-identifier',
        'after_element_html' => '<div class="hint"><p class="note">'.$this->__('Exemple. something_1, menu5, id-4.').'</p></div>',
     ));


    // Statut
    $fieldset->addField('status', 'select', array(
        'label' => Mage::helper('fluidmenu')->__('Status'),
        'name' => 'status',
        'values' => array(
            array(
                'value' => 1,
                'label' => Mage::helper('fluidmenu')->__('Enabled'),
            ),
            array(
                'value' => 2,
                'label' => Mage::helper('fluidmenu')->__('Disabled'),
            ),
        ),
    ));

    // Store
    if (!Mage::app()->isSingleStoreMode()) {
        $fieldset->addField('store_id', 'multiselect', array(
            'name' => 'stores[]',
            'label' => Mage::helper('cms')->__('Store View'),
            'title' => Mage::helper('cms')->__('Store View'),
            'required' => true,
            'values' => Mage::getSingleton('adminhtml/system_store')
                         ->getStoreValuesForForm(false, true),
        ));
    }


    // Description
    $fieldset->addField('description', 'editor', array(
        'label' => Mage::helper('fluidmenu')->__('Description'),
        'required' => false,
        'name' => 'description',
    ));

    $form->setValues(Mage::registry('fluidmenu_data')->getData());

    return parent::_prepareForm();
  }

}