<?php
class PWS_ProductRegistration_Model_Mysql4_Productregistration_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        $this->_init('pws_productregistration/productregistration');
    }


    public function joinCustomers()
    {
        $customer = Mage::getResourceSingleton('customer/customer');

        // get first name
        $firstnameAttr = $customer->getAttribute('firstname');
        $firstnameAttrId = $firstnameAttr->getAttributeId();
        $firstnameTable = $firstnameAttr->getBackend()->getTable();

        if ($firstnameAttr->getBackend()->isStatic()) {
            $firstnameField = 'firstname';
            $attrCondition = '';
        } else {
            $firstnameField = 'value';
            $attrCondition = ' AND _table_customer_firstname.attribute_id = '.$firstnameAttrId;
        }

        $this->getSelect()->joinInner(array('_table_customer_firstname' => $firstnameTable),
            '_table_customer_firstname.entity_id=main_table.customer_id'.$attrCondition, array());

		// get email
		$emailAttr = $customer->getAttribute('email');
        $emailAttrId = $emailAttr->getAttributeId();
        $emailTable = $emailAttr->getBackend()->getTable();

        if ($emailAttr->getBackend()->isStatic()) {
            $emailField = 'email';
            $attrCondition = '';
        } else {
            $emailField = 'value';
            $attrCondition = ' AND _table_customer_email.attribute_id = '.$emailAttrId;
        }

        $this->getSelect()->joinInner(array('_table_customer_email' => $emailTable),
            '_table_customer_email.entity_id=main_table.customer_id'.$attrCondition, array());


		// get lastname
        $lastnameAttr = $customer->getAttribute('lastname');
        $lastnameAttrId = $lastnameAttr->getAttributeId();
        $lastnameTable = $lastnameAttr->getBackend()->getTable();

        if ($lastnameAttr->getBackend()->isStatic()) {
            $lastnameField = 'lastname';
            $attrCondition = '';
        } else {
            $lastnameField = 'value';
            $attrCondition = ' AND _table_customer_lastname.attribute_id = '.$lastnameAttrId;
        }


        $this->getSelect()->joinInner(
        	array('_table_customer_lastname' => $lastnameTable),
            '_table_customer_lastname.entity_id=main_table.customer_id'.$attrCondition, array())
            ->from("", array(
                        'customer_name' => "CONCAT(_table_customer_firstname.{$firstnameField}, ' ', _table_customer_lastname.{$lastnameField})",
                        'customer_email'	=> $emailField
                        )
                  );


        //echo $this->getSelect()->__toString();

        return $this;
    }

    public function joinProducts()
    {
        $resource = Mage::getSingleton('core/resource');
        $product_table = $resource->getTableName('catalog/product');

        $productResource = Mage::getResourceSingleton('catalog/product');
        $nameAttr = $productResource->getAttribute('name');
        $nameAttrId = $nameAttr->getAttributeId();

        $nameAttrTable = $nameAttr->getBackend()->getTable();


    	$this->getSelect()->joinLeft(array('_table_product' => $product_table),
            '_table_product.entity_id=main_table.product_id', array('sku'));

        // the main_table.product_name contains the product name in case config register product input (product name) was used
    	$this->getSelect()->joinLeft(
        	array('_table_product_name' => $nameAttrTable),
            '_table_product_name.entity_id=main_table.product_id
                AND _table_product_name.store_id = 0
                AND _table_product_name.attribute_id = '.(int)$nameAttrId, array())
            ->from("",array(
                        'actual_product_name' => "if(length(_table_product_name.value) > 0, _table_product_name.value, main_table.product_name)",
                        )
            );


    	//echo ($this->getSelect()->__toString());
        //die;
        return $this;

    }


    public function load($printQuery = false, $logQuery = false)
    {
        $this->_renderFilters()
             ->_renderOrders()
             ->_renderLimit();

        if ($this->isLoaded()) {
            return $this;
        }

        $this->_renderFilters()
             ->_renderOrders()
             ->_renderLimit();

        $data = $this->getData();
        $this->resetData();

        if (is_array($data)) {
            foreach ($data as $row) {
                $item = $this->getNewEmptyItem();


                if ($this->getIdFieldName()) {
                    $item->setIdFieldName($this->getIdFieldName());
                }
                $item->addData($row);
                try{
                $this->addItem($item);
                }catch(Exception $e){}
            }
        }
        $this->_setIsLoaded();
        $this->_afterLoad();
        return $this;
    }

}
