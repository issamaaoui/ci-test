<?php

class PWS_ProductRegistration_Model_Api2_Productregistration_Rest_Customer_V1 extends PWS_ProductRegistration_Model_Api2_Productregistration_Rest
{

    protected function _create(array $data)
    {
        if (key_exists('serial_number', $data)) {
            $result = $this->_insertProduct($data);
        }
        echo $this->getRenderer()->render(($this->_retrieveCollection()));
        die();
    }

    protected function _multiCreate(array $data)
    {
        foreach ($data as $item) {
            if (key_exists('serial_number', $data)) {
                $result = $this->_insertProduct($item);
            }
        }
        echo $this->getRenderer()->render(($this->_retrieveCollection()));
        die();
    }

    protected function _insertProduct(array $data)
    {
        $customerId = $this->getApiUser()->getUserId();
        $serialNumber = $data['serial_number'];
        $eeee = substr($serialNumber,-4);
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        
        $table = $resource->getTableName('pws_productregistration/productregistration');
        $query = 'SELECT count(*) FROM '.$table.' WHERE customer_id = "' . $customerId . '" AND serial_number = "'.$serialNumber.'" LIMIT 1';
        $nbSerialNumber = $readConnection->fetchOne($query);
        if($nbSerialNumber > 0){
            die('{"messages":{"error":[{"code":202,"message":"Serial Number existing."}]}}');
        }

	    $table = $resource->getTableName('pws_productregistration/serial_numbers');
	    $query = 'SELECT sku FROM '.$table.' WHERE valid_serial_number = "' . $eeee . '"LIMIT 1';
	    $productSku = $readConnection->fetchOne($query);

        try {
            if(!empty($productSku)){
                $productId = Mage::getModel('catalog/product')->loadByAttribute('sku', $productSku)->getId();
                $productregistration = Mage::getModel('pws_productregistration/productregistration');

                $data['customer_id'] = $customerId;
                $data['product_id'] = $productId;
                $data['date_of_purchase'] = now();
                $data['purchased_from'] = 'WEB';
                $productregistration->setData($data);
                $productregistration->save();
            }else{
                die('{"messages":{"error":[{"code":201,"message":"Serial Number Unknown."}]}}');
            }
            
            
        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
        return $this->_getLocation($productregistration);
    }

    protected function _retrieve()
    {
        $customerId = $this->getApiUser()->getUserId();
        $productRegistration = $this->_loadProductRegistrationByCustomerId($customerId);
        return $productRegistration->getData();
    }

    protected function _retrieveCollection()
    {
        $registeredProductsCollection = Mage::getModel('pws_productregistration/productregistration')->getCollection()->addFieldToFilter('customer_id', $this->getApiUser()
            ->getUserId());
        $data = $registeredProductsCollection->load()->toArray();
        return isset($data['items']) ? $data['items'] : $data;
    }
}