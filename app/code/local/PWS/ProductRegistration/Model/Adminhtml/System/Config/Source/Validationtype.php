<?php
class PWS_ProductRegistration_Model_Adminhtml_System_Config_Source_Validationtype
{
    const VALIDATION_TYPE_NONE         = 'none';
    const VALIDATION_TYPE_RULE         = 'rule';
    const VALIDATION_TYPE_EXACT_MATCH  = 'exact_match';
    const VALIDATION_TYPE_CALLBACK     = 'callback';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::VALIDATION_TYPE_NONE,        'label' => Mage::helper('pws_productregistration')->__('None')),
            array('value' => self::VALIDATION_TYPE_RULE,        'label' => Mage::helper('pws_productregistration')->__('Rule')),
            array('value' => self::VALIDATION_TYPE_EXACT_MATCH, 'label' => Mage::helper('pws_productregistration')->__('Exact match')),
            array('value' => self::VALIDATION_TYPE_CALLBACK,    'label' => Mage::helper('pws_productregistration')->__('Callback')),
        );
    }

}
