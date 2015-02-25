<?php
class PWS_ProductRegistration_Model_Observer
{

    /**
     * Observes core_block_abstract_to_html_before
     *
     * Adds a new tab to customer edit view and register product button
     *
     * @param   Varien_Event_Observer $observer array('block' => $this)
     * @return  PWS_ProductRegistration_Model_Observer
     */
    public function addCustomerProductRegistrationTab($observer)
    {

        $block = $observer->getEvent()->getBlock();

        if ($block->getData('type') == 'adminhtml/customer_edit') {
            $block->addButton('register_product', array(
                'label' => Mage::helper('customer')->__('Register Product'),
                'onclick' => 'setLocation(\'' . $block->getUrl('adminhtml/productregistration/add', array('customer_id' => $block->getCustomerId())) . '\')',
                'class' => 'add',
            ), -1);
        }

        if ($block->getId() != 'customer_info_tabs') return $this;

        $block->addTab('custom_message', array(
            'label'     => Mage::helper('customer')->__('Registered Products'),
            'content'   => $block->getLayout()->createBlock('pws_productregistration/adminhtml_customer_edit_tab_registeredproducts')
                ->toHtml(),
        ),'tags');

        return $this;
    }


    /**
     * Observes pws_productregistration_serial_number_validation
     *
     * Note: not used, added only for documentation purposes;
     * the pws_productregistration_serial_number_validation event is commented in config.xml
     *
     * @param Varien_Event_Observer  $observer array(product, serial_number, is_valid)
     *
     */
    public function validateSerialNumber($observer)
    {
        $data         = $observer->getEvent()->getValidationData();
        $serialNumber = $data->getSerialNumber();
        $product      = $data->getProduct();

        // check if serial number is valid here ...

        $data->setIsValid(true);
    }


}
