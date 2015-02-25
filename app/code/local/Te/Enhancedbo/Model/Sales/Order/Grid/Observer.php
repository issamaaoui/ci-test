<?php
class Te_Enhancedbo_Model_Sales_Order_Grid_Observer
{
	protected $_billingAliasName = 'billing_o_a';
	protected $_shippingAliasName = 'shipping_o_a';
    public function collectionBeforeLoad($observer)
    {
	    $collection = $observer->getOrderGridCollection();
	    
	    $billingAliasName = $this->_billingAliasName;
        $shippingAliasName = $this->_shippingAliasName;
        $joinTable = $collection->getTable('sales/order_address');


        $collection->addFilterToMap('billing_country_id', $billingAliasName . '.country_id');

        $collection->addFilterToMap('shipping_country_id', $shippingAliasName . '.country_id');

         $collection->getSelect()
            ->joinLeft(
                array($billingAliasName => $joinTable),
                "(main_table.entity_id = {$billingAliasName}.parent_id"
                    . " AND {$billingAliasName}.address_type = 'billing')",
                array(
                    $billingAliasName."_country_id" => $billingAliasName . '.country_id'
                )
            )
            ->joinLeft(
                array($shippingAliasName => $joinTable),
                "(main_table.entity_id = {$shippingAliasName}.parent_id"
                    . " AND {$shippingAliasName}.address_type = 'shipping')",
                array(
                	$shippingAliasName . '_country_id'=> $shippingAliasName . '.country_id'
                )
            );

	    $select = $collection->getSelect();
	    $select->join(array('oe' => $collection->getTable('sales/order')), 'oe.entity_id=main_table.entity_id', array('oe.sapbyd_order_id'));
	  	
	    return $collection;  	      
    }


    public function addColumn($observer)
    {
        $block = $observer->getBlock();
         $column = $observer->getColumn();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid)
        {
           

            if(!$column->getData('filter_index'))
            {
                $column->setData('filter_index', "main_table.".$column->getIndex());
            }
        }
        return $column;

    }


    public function prepareLayout($observer)
    {
    	$block = $observer->getBlock();
	    if (!isset($block)) {
	        return $this;
	    }

	   
	    if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid) {
	    	$billingAliasName = $this->_billingAliasName;
        	$shippingAliasName = $this->_shippingAliasName;

        	$block->addColumnAfter('country_id', array(
	            'header' => Mage::helper('sales')->__('Country'),
	            'index' => $billingAliasName."_country_id",
	            'type'  => 'options',
	            'width' => '70px',
	            'filter_index' => $billingAliasName.'.country_id',
	            'options' => $this->_getCountries(),
	        ), "shipping_name");	

	        $block->addColumnAfter('sapbyd_order_id', array(
	            'header' => Mage::helper('sales')->__('# SAP'),
	            'index' => "sapbyd_order_id",
	            'width' => '80px',
	            'type'  => 'text',
                'filter_index' => 'oe.sapbyd_order_id',
	        ), "real_order_id");

	        $block->addColumnAfter('products', array(
	            'header' => Mage::helper('sales')->__('Products'),
	            'index' => "entity_id",
	            'width' => '150px',
	            'type'  => 'text',
	            'renderer' => 'Te_Enhancedbo_Block_Adminhtml_Sales_Order_Grid_Renderer_Products',
	            'filter' => false,
	        ), "grand_total");	
    	}

    	return $block;

    }

    protected function _getCountries()
    {
    	$result= array();
    	foreach(Mage::getResourceModel('directory/country_collection') as $country)
    	{
    		$result[$country->getId()] = $country->getName();
    	}

    	
    	return $result;

    }
}