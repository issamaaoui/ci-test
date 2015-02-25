<?php
class PWS_ProductRegistration_Block_Adminhtml_ProductRegistration_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldsetClient = $form->addFieldset('product_registration_client_form', array(
            'legend'=>Mage::helper('pws_productregistration')->__('Customer Information')
        ));

        $fieldsetClient->addField('customer_id', 'text', array(
            'name'      => 'product_registration[customer_id]',
            'label'     => Mage::helper('pws_productregistration')->__('ID'),
            'readonly'  => true,
            'note'		=> 'read-only',
        ));

        $fieldsetClient->addField('customer_name', 'text', array(
            'name'      => 'product_registration[customer_name]',
            'label'     => Mage::helper('pws_productregistration')->__('Name'),
            'readonly'  => true,
            'note'		=> 'read-only',

        ));

        $fieldsetClient->addField('customer_email', 'text', array(
            'name'      => 'product_registration[customer_email]',
            'label'     => Mage::helper('pws_productregistration')->__('Email Address'),
            'readonly'  => true,
            'note'		=> 'read-only',

        ));

        $fieldsetProduct = $form->addFieldset('product_registration_product_form', array(
            'legend'=>Mage::helper('pws_productregistration')->__('Product Information')
        ));

        if (Mage::helper('pws_productregistration')->useProductSkuInput()) {
            $fieldsetProduct->addField('product_sku', 'text', array(
                'name'      => 'product_registration[product_sku]',
                'label'     => Mage::helper('pws_productregistration')->__('Product SKU'),
            ));
        } elseif (Mage::helper('pws_productregistration')->useProductNameInput()) {
            $fieldsetProduct->addField('product_name', 'text', array(
                'name'      => 'product_registration[product_name]',
                'label'     => Mage::helper('pws_productregistration')->__('Product Name'),
            ));
        } else {
            $options = Mage::helper('pws_productregistration')->getRegistrableProductOptions();

            $fieldsetProduct->addField('product_id', 'select', array(
                'name'      => 'product_registration[product_id]',
                'label'     => Mage::helper('pws_productregistration')->__('Product'),
                'values'    => $options,
            ));
        }

        $fieldsetProduct->addField('serial_number', 'text', array(
            'name'      => 'product_registration[serial_number]',
            'label'     => Mage::helper('pws_productregistration')->__('Serial Number'),
        ));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $fieldsetProduct->addField('date_of_purchase', 'date', array(
            'name'         => 'product_registration[date_of_purchase]',
            'label'        => Mage::helper('pws_productregistration')->__('Date of Purchase'),
            'class'        => 'required-entry',
            'required'     => true,
            'image'        => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso,

        ));

        $fieldsetProduct->addField('purchased_from', 'text', array(
            'name'      => 'product_registration[purchased_from]',
            'label'     => Mage::helper('pws_productregistration')->__('Purchased From'),
            'class'     => 'required-entry',
            'required'  => true,
        ));

        // only when editing
        if (Mage::registry('product_registration')) {
            $fieldsetProduct->addField('is_valid', 'select', array(
                'name'      => 'product_registration[is_valid]',
                'label'     => Mage::helper('pws_productregistration')->__('Is Valid'),
                'class'     => 'required-entry',
                'required'  => false,
                'options'   => array(
                    'yes' => $this->__('yes'),
                    'no'  => $this->__('no')
                ),
            ));
        }

        $fieldsetProduct->addField('notes', 'textarea', array(
            'name'      => 'product_registration[notes]',
            'label'     => Mage::helper('pws_productregistration')->__('Notes'),
        ));

		if (Mage::registry('product_registration')){
			$customer_info = Mage::getModel('customer/customer')->load(Mage::registry('product_registration')->getCustomerId());

			$product_registration_data                   = Mage::registry('product_registration')->getData();
			$product_registration_data['customer_email'] = $customer_info['email'];
			$product_registration_data['customer_name']  = $customer_info['firstname'].' '.$customer_info['lastname'];

            if (Mage::helper('pws_productregistration')->useProductSkuInput()) {
                $product = Mage::getModel('catalog/product')->load(Mage::registry('product_registration')->getProductId());

                if (empty($product_registration_data['product_sku'])) {
                    $product_registration_data['product_sku']    = $product->getSku();
                }
            }

        	$form->setValues($product_registration_data);
        }


        return parent::_prepareForm();
    }



    protected function _toHtml()
    {
        return parent::_toHtml();
    }


}
