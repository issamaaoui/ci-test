<?php
class PWS_ProductRegistration_Model_Adminhtml_System_Config_Source_Productinput
{
    const PRODUCT_FIEDL_SELECT     = 'select';
    const PRODUCT_FIELD_INPUT_SKU  = 'input_sku';
    const PRODUCT_FIELD_INPUT_NAME = 'input_name';
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::PRODUCT_FIEDL_SELECT,     'label' => Mage::helper('pws_productregistration')->__('Select box (registrable magento product)')),
            array('value' => self::PRODUCT_FIELD_INPUT_SKU,  'label' => Mage::helper('pws_productregistration')->__('Input field (sku - registrable magento product)')),
            /*array('value' => self::PRODUCT_FIELD_INPUT_NAME, 'label' => Mage::helper('pws_productregistration')->__('Input field (product name - anything)')),*/
        );
    }

}
