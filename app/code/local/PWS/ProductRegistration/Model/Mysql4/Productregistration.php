<?php
class PWS_ProductRegistration_Model_Mysql4_Productregistration extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('pws_productregistration/productregistration','registration_id');
    }

    public function saveRegisteredProducts($customer_id, $data)
    {
    	//remove previous data
    	//$deleteCondition = $this->_getWriteAdapter()->quoteInto('customer_id=?', $customer_id);
        //$this->_getWriteAdapter()->delete($this->getMainTable(), $deleteCondition);


    	//add new data
    	if(is_array($data)){
			foreach($data as $registered_product){

				$date = new Zend_Date($registered_product['date_of_purchase'], null, Mage::app()->getLocale()->getLocaleCode());

				$registered_product['date_of_purchase'] = $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

			    $record = array(
		            'customer_id'       => $customer_id,
		            'product_id'        => $registered_product['product_id'],
                    'product_name'      => $registered_product['product_name'],
                    'store_id'          => $registered_product['store_id'],
		            'serial_number'     => $registered_product['serial_number'],
		            'date_of_purchase'  => $registered_product['date_of_purchase'],
		            'purchased_from'    => $registered_product['purchased_from'],
		            'created_on'        => $this->formatDate(time()),
		        );

				$this->_getWriteAdapter()->insert($this->getMainTable(), $record);
			}
    	}
    }


    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getCreatedOn()) {
            $object->setCreatedOn($this->formatDate(time()));
        }

        //$object->setUpdatedOn($this->formatDate(time()));
        parent::_beforeSave($object);
    }

}
