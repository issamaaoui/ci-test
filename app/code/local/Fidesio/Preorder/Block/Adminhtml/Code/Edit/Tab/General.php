<?php

class Fidesio_Preorder_Block_Adminhtml_Code_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('code_form', array('legend' => Mage::helper('preorder')->__('Information du code')));
        $data = Mage::registry('code_data')->getData();

        // Statut
        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('adminhtml')->__('Status'),
            'name' => 'status',
            'values' => Fidesio_Preorder_Model_Code::getStatusOption(),
        ));

        // Code
        $fieldset->addField('code', 'text', array(
            'label' => Mage::helper('preorder')->__('Shopping code'),
            'required' => true,
            'name' => 'code',
        ));

        // Order
        $fieldset->addField('order_id', 'text', array(
            'label' => Mage::helper('adminhtml')->__('Order'),
            'name' => 'order_id',
        ));

        // Customer
        $fieldset->addField('customer_id', 'text', array(
            'label' => Mage::helper('adminhtml')->__('Customer'),
            'name' => 'customer_id',
        ));

        $form->setValues($data);
        return parent::_prepareForm();
    }

}