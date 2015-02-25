<?php
 
class Dvl_Sapbyd_Block_Adminhtml_Sapbyd_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('sapbyd_form', array('legend'=>Mage::helper('sapbyd')->__('Item information')));
       
        $fieldset->addField('title', 'text', array(
            'label'     => Mage::helper('sapbyd')->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'title',
        ));
 
        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('sapbyd')->__('Status'),
            'name'      => 'status',
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('sapbyd')->__('Active'),
                ),
 
                array(
                    'value'     => 0,
                    'label'     => Mage::helper('sapbyd')->__('Inactive'),
                ),
            ),
        ));
       
        $fieldset->addField('content', 'editor', array(
            'name'      => 'content',
            'label'     => Mage::helper('sapbyd')->__('Content'),
            'title'     => Mage::helper('sapbyd')->__('Content'),
            'style'     => 'width:98%; height:400px;',
            'wysiwyg'   => false,
            'required'  => true,
        ));
       
        if ( Mage::getSingleton('adminhtml/session')->getSapbydData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getSapbydData());
            Mage::getSingleton('adminhtml/session')->setSapbydData(null);
        } elseif ( Mage::registry('sapbyd_data') ) {
            $form->setValues(Mage::registry('sapbyd_data')->getData());
        }
        return parent::_prepareForm();
    }
}