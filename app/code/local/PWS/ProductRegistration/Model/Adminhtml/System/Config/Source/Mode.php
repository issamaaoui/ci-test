<?php
class PWS_ProductRegistration_Model_Adminhtml_System_Config_Source_Mode
{
    const MODE_UPDATE = 'update';
    const MODE_ADD = 'add';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::MODE_UPDATE, 'label'=>Mage::helper('pws_productregistration')->__('Update')),
            array('value' => self::MODE_ADD, 'label'=>Mage::helper('pws_productregistration')->__('Add')),
        );
    }

}