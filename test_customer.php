<?php 
require_once("app/Mage.php");
umask(0);
Mage::app();
$order = mage::getModel("sales/order")->load(71);


/*** Création client ***/
$customerId = $order->getCustomerId();
$customer = Mage::getModel('customer/customer');
$customer->load($customerId);

if(!$customer->hasData('sapbyd_customer_id'))
{
    $sapbydCustomerId = addCustomer($customer, $order);
}else{
    $sapbydCustomerId = $customer->getData('sapbyd_customer_id');
}

 $payment = $order->getPayment();

 $transactions = mage::getResourceModel("payboxcw/transaction_collection")->addFieldToFilter("order_id", $order->getId());
 
 $transaction = mage::getModel("payboxcw/transaction"); 
 if($transactions->getSize() > 0)
	$transaction = $transactions->getFirstItem();



 $sapbydOrder = new Dvl_Sapbyd_Model_Soap_Order('manage');
 $sapbydOrder = $sapbydOrder->setModelData($sapbydCustomerId, $customer, $payment, $transaction);
 $response = $sapbydOrder->request();
 print_r($response);
if(empty($response->error) & (!empty($response->ID)))
{
   
    $sapbydOrderId = $response->ID;
    //Message for email
    $message = "NEW ORDER" . chr(13);
    $message .= "------------------------" . chr(13);
    $message .= "Magento Order ID : " . $order->getIncrementId() . chr(13);
    $message .= "SAP Order ID : " . $sapbydOrderId . chr(13);
    
    $sapOrderData = array(
        'sapbyd_order_id'      => $sapbydOrderId,
        'sapbyd_customer_id'   => $sapbydCustomerId,
        'sapbyd_code'                 => 'NEW',
        'sapbyd_message'              => $message
    );

	$order->setData(array_merge($order->getData(), $sapOrderData));

    $order->save();
}

echo "--------------CLIENT---------------";
echo "<pre>".print_r($customer->getData(), true)."</pre>";
echo "--------------COMMANDE-------------";
echo "<pre>".print_r($order->getData(), true)."</pre>";
	




	



	function addCustomer(Mage_Core_Model_Abstract $customer, Mage_Sales_Model_Order $order){
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

	        
	        $sapData = array(
	            'sapbyd_customer_id'   => $sapbydCustomerId,
	            'sapbyd_code'                 => 'NEW',
	            'sapbyd_message'              => $message,
	        );


	        $customer ->setData(array_merge($customer->getData(), $sapData));
	        $customer->save();

	        return $result->InternalID;
	    }else{
	        return null;
	    }
	}

