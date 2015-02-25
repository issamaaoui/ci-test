<?php
abstract class PWS_ProductRegistration_Model_Api2_Productregistration_Rest extends PWS_ProductRegistration_Model_Api2_Productregistration
{
    protected function _retrieve()
    {
        $customerId = $this->getApiUser()->getUserId();
        $productRegistration = $this->_loadProductRegistrationByCustomerId($customerId);
        return $productRegistration->getData();
    }
    
    protected function _loadProductRegistrationByCustomerId($id)
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $productregistration = Mage::getModel('pws_productregistration/productregistration')->load($id, 'customer_id');
        if (!$productregistration->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return $productregistration;
    }
    
    protected function _loadProductRegistrationBySerialNumber($sn, $customerId)
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $productregistration = Mage::getModel('pws_productregistration/productregistration')
        ->getCollection()
        ->addFieldToFilter('serial_number',$sn)
        ->addFieldToFilter('customer_id',$customerId);
        return $productregistration;
    }

}