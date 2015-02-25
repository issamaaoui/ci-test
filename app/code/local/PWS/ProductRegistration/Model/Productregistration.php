<?php
class PWS_ProductRegistration_Model_Productregistration extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('pws_productregistration/productregistration');
    }

    /**
     * Validate serial numbers
     *
     * @param array $registeredProducts
     * @param int $existentRegistrationId - this is used when a product registration is edited
     *
     * @return bool
     */
    public function validate($registeredProducts, $existentRegistrationId = 0)
    {
        $errors = array();

        $addeSerialNumbers = array();
        foreach ($registeredProducts as $registeredProduct) {

            // get product id by serial number
            if (Mage::helper('pws_productregistration')->useProductSkuInput()) {
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $registeredProduct['product_sku']);

                if (!$product || !$product->getId()) {
                    $msg = Mage::helper('pws_productregistration')->__(
                        "Invalid sku %s",
                        $registeredProduct['product_sku']
                    );
                    $errors[] = $msg;
                    break;
                }

                $registeredProduct['product_id'] = $product->getId();

            } elseif (Mage::helper('pws_productregistration')->useProductNameInput()) {
                $product = Mage::getModel('catalog/product');
                if (!empty($registeredProduct['product_id'])) {
                    $product->load($registeredProduct['product_id']);
                } else {
                    $product->setSku($registeredProduct['product_name']);
                }
                $product->setName($registeredProduct['product_name']);

            } else {
                $product = Mage::getModel('catalog/product')->load($registeredProduct['product_id']);

                if (!$product->getData('is_registrable')) {
                    $msg = Mage::helper('pws_productregistration')->__(
                        "Product %s with sku %s cannot be registered",
                        $product->getName(),
                        $product->getSku()
                    );
                    $errors[] = $msg;
                    break;
                }
            }

            $registeredProduct['serial_number'] = trim($registeredProduct['serial_number']);

            // validate serial number from list
            if (Mage::helper('pws_productregistration')->isSerialNumberValidationEnabled()) {

                $result  = $this->validateSerialNumber($product, $registeredProduct['serial_number']);

                if (!$result) {
                    $msg = Mage::helper('pws_productregistration')->__(
                        "Invalid serial number %s for product %s",
                        $registeredProduct['serial_number'],
                        $product->getName()
                    );
                    $errors[] = $msg;
                    break;
                }
            }

            if (empty($registeredProduct['serial_number'])) return $errors;

            if (Mage::helper('pws_productregistration')->allowDuplicateSerialNumbers()) break;

            // check if two products the user have added have the same serial number
            if (isset($addedSerialNumbers[strtolower(trim($registeredProduct['serial_number']))])) {
                $msg = Mage::helper('pws_productregistration')->__(
                    "Serial number %s is already used for product %s, please add a different serial number",
                    $registeredProduct['serial_number'],
                    $addedSerialNumbers[$registeredProduct['serial_number']]
                );
                $errors[] = $msg;
                break;
            } else {
                $addedSerialNumbers[strtolower($registeredProduct['serial_number'])] = $product->getName();
            }

            // check if serial number is already registered
            $collection = Mage::getModel('pws_productregistration/productregistration')->getCollection()
                ->addFieldToFilter('serial_number', trim($registeredProduct['serial_number']));

            if ($collection->getSize()) {
                if ($collection->getFirstItem()->getId() != $existentRegistrationId) {

                    $msg = Mage::helper('pws_productregistration')->__(
                        "Product with serial number %s is already registered, please add a different serial number",
                        $registeredProduct['serial_number']
                    );
                    $errors[] = $msg;
                    break;
                }
            }
        }

        return $errors;
    }

    public function validateSerialNumber($product, $serialNumber)
    {
        $config = Mage::helper('pws_productregistration')->getConfig();
        $model  = Mage::getModel('pws_productregistration/adminhtml_system_config_source_validationtype');

        if ($config['validation_type'] == PWS_ProductRegistration_Model_Adminhtml_System_Config_Source_Validationtype::VALIDATION_TYPE_NONE) {
            return true;
        }

        if ($config['validation_type'] == PWS_ProductRegistration_Model_Adminhtml_System_Config_Source_Validationtype::VALIDATION_TYPE_EXACT_MATCH) {
            $sku               = $product->getSku();
            $serialNumberModel = Mage::getModel('pws_productregistration/adminhtml_system_config_backend_serialnumbers');
            return $serialNumberModel->isValidSerialNumber($sku, $serialNumber);
        }

        if ($config['validation_type'] == PWS_ProductRegistration_Model_Adminhtml_System_Config_Source_Validationtype::VALIDATION_TYPE_RULE) {
            $pattern = '#'.trim($config['validation_rule']).'#';
            if (preg_match($pattern, $serialNumber)) return true;
            return false;
        }

        if ($config['validation_type'] == PWS_ProductRegistration_Model_Adminhtml_System_Config_Source_Validationtype::VALIDATION_TYPE_CALLBACK) {
            $data = array(
                'product'       => $product,
                'serial_number' => $serialNumber,
                'is_valid'      => true,
            );
            $eventData = new Varien_Object($data);
            Mage::dispatchEvent('pws_productregistration_serial_number_validation', array('validation_data' => $eventData));
            return $eventData->getIsValid();
        }

        return true;
    }

    public function saveRegisteredProducts($customer_id, $data)
    {
    	if ($data) {
    		$this->_getResource()->saveRegisteredProducts($customer_id, $data);
    	}
    	return $this;
    }
}
