<?php
class Dvl_Sapbyd_Model_Observer
{
	public function addOrder(Varien_Event_Observer $observer)
	{
	    $transaction = $observer->getTransaction();
		$order = $transaction->getOrder();
		if($order->getState() == Mage_Sales_Model_Order::STATE_PROCESSING){
		    $payment = $order->getPayment();
    		try {
    		    $customerId = $order->getCustomerId();
    
    		    $customer = Mage::getModel('customer/customer');
    		    $customer->load($customerId);
    		    
    		    // Customer exists ?
    		    $mageSapbydCustomer = Mage::getModel('dvl_sapbyd/customer');
    		    $mageSapbydCustomer->load($customerId, 'customer_id');
    		    if(!$mageSapbydCustomer->hasData('sapbyd_customer_id')){
    		        $sapbydCustomerId = $this->addCustomer($customer, $order);
    		    }else{
    		        $sapbydCustomerId = $mageSapbydCustomer->getData('sapbyd_customer_id');
    		    }
    
    		    $sapbydOrder = new Dvl_Sapbyd_Model_Soap_Order('manage');
    		    $sapbydOrder = $sapbydOrder->setModelData($sapbydCustomerId, $customer, $payment, $transaction);
    		    $response = $sapbydOrder->request();
    
    		    if(empty($response->error) & (!empty($response->ID))){
    		        $mageSapbydOrder = Mage::getModel('dvl_sapbyd/order');
    		        $sapbydOrderId = $response->ID;
    		        //Message for email
    		        $message = "NEW ORDER" . chr(13);
    		        $message .= "------------------------" . chr(13);
    		        $message .= "Magento Order ID : " . $order->getIncrementId() . chr(13);
    		        $message .= "SAP Order ID : " . $sapbydOrderId . chr(13);
    		        
    		        $mageSapbydOrder->addData(array(
    		            'order_id'             => $order->getId(),
    		            'sapbyd_order_id'      => $sapbydOrderId,
    		            'customer_id'          => $customerId,
    		            'sapbyd_customer_id'   => $sapbydCustomerId,
    		            'code'                 => 'NEW',
    		            'message'              => $message,
    		            'created_on'           => $mageSapbydOrder->getResource()->formatDate(time()),
    		            'updated_on'           => $mageSapbydOrder->getResource()->formatDate(time()),
    		        ));		        
    		        $mageSapbydOrder->saveOrder();
    		    }
    	   } catch (Exception $e) {
    		    $test = new Dvl_Sapbyd_Model_Log();
    		    $test->setData("message","error");
    		    $test->saveLog();
    		    die($e);
    	   }
		}else{
		    //print_r($order->getState());
		    //die();
		    
		}
	}
	
	public function addCustomer(Mage_Core_Model_Abstract $customer, Mage_Sales_Model_Order $order){
        // Récuperer le $countryCode de l'adresse de livraison de la commande
        if (version_compare(Mage::getVersion(), '1.3.0', '>')) {
            $billingId = $order->getBillingAddress()->getId();
            $address = Mage::getModel('sales/order_address')->load($billingId);
        }
        else
            $address = $order->getBillingAddress();

	    // add Customer SAP ByD
	    $sapbydCustomer = new Dvl_Sapbyd_Model_Soap_Customer('manage');
	    $sapbydCustomer->setModelData($customer, $order->getStore()->getWebsite()->getCode(), $address->getCountryId());
	    $result = $sapbydCustomer->request();
	    if(!empty($result->InternalID)){
	        $sapbydCustomerId = $result->InternalID;
	        $customerId = $customer->getId();
	        //Message for email
	        $message = "NEW CUSTOMER" . chr(13);
	        $message .= "------------------------" . chr(13);
	        $message .= $customer->getFirstname() . ' ' .$customer->getLastname() . chr(13);
	        $message .= "------------------------" . chr(13);
	        $message .= "Magento Customer ID : " . $customer->getIncrementId() . chr(13);
	        $message .= "SAP Customer ID : " . $sapbydCustomerId . chr(13);

	        $mageSapbydCustomer = Mage::getModel('dvl_sapbyd/customer');
	        $mageSapbydCustomer->addData(array(
	            'customer_id'          => $customerId,
	            'sapbyd_customer_id'   => $sapbydCustomerId,
	            'code'                 => 'NEW',
	            'message'              => $message,
	            'created_on'           => $mageSapbydCustomer->getResource()->formatDate(time()),
	            'updated_on'           => $mageSapbydCustomer->getResource()->formatDate(time())
	        ));
	        $mageSapbydCustomer->saveCustomer();
	        return $result->InternalID;
	    }else{
	        return null;
	    }
	}
	
	public function bypassCart(Varien_Event_Observer $observer){
	    //Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('checkout/onepage', array('_secure'=>true)));
	}

	public function bypassCartControllerActionPostDispatch(Varien_Event_Observer $observer){
	    if($observer->getEvent()->getControllerAction()->getFullActionName() == 'checkout_cart_add')
	    {
	        //Mage::dispatchEvent("add_to_cart_after", array('request' => $observer->getControllerAction()->getRequest()));
	    }
	}
}
