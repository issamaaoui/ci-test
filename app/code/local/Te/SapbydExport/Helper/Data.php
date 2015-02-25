<?php
class Te_SapbydExport_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getEnvironment()
    {
        return mage::getStoreConfig("te_sapbydexport/general/environment");
    }

    public function getEmailRecipients()
    {
        return mage::getStoreConfig("te_sapbydexport/general/email_recipient");
    }

    public function getEmailTemplate()
    {
        return mage::getStoreConfig("te_sapbydexport/general/email_template");
    }

    /**
    *
    * Export Order
    *
    **/
    public function exportOrder($order)
    {

        try
        {
            $customerId = $order->getCustomerId();
            $customer = Mage::getModel('customer/customer');
            $customer->load($customerId);
            if(!$customer->hasData('sapbyd_customer_id'))
            {
                $sapbydCustomerId = $this->addCustomer($customer, $order);
            }else{
                $sapbydCustomerId = $customer->getData('sapbyd_customer_id');
            }

            if($sapbydCustomerId)
            {

                $payment = $order->getPayment();

                $transactions = mage::getResourceModel("payboxcw/transaction_collection")->addFieldToFilter("order_id", $order->getId());
         
                $transaction = mage::getModel("payboxcw/transaction"); 
                if($transactions->getSize() > 0)
                    $transaction = $transactions->getFirstItem();

                $sapbydOrder = new Dvl_Sapbyd_Model_Soap_Order('manage');
                $sapbydOrder = $sapbydOrder->setModelData($sapbydCustomerId, $customer, $payment, $transaction);
                $response = $sapbydOrder->request();
                

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

                    $this->log($message, Zend_Log::DEBUG);
                    
                }else{
                    $response = new SimpleXMLElement($response);
                    $message = array();
                    $errors = $response->xpath('//Item');

                    foreach($errors as $error)
                    {
                        $message[]= $error->Note->__toString();
                    }
                    
                    $sapOrderData = array(
                        'sapbyd_customer_id'   => $sapbydCustomerId,
                        'sapbyd_code'                 => 'ERROR',
                        'sapbyd_message'              => implode("\n",$message)
                    );
                    Mage::getSingleton('core/session')->addError(implode("\n",$message));
                    $this->log(implode("\n",$message), Zend_Log::ERR);
                }
                
                $order->setData(array_merge($order->getData(), $sapOrderData));
                $order->save();
            }
        }catch(Exception $e){
            Mage::logException($e);
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
    }

    protected function _initAddress($customer, $order = null)
    {
        $address = null;
        if($order)
        {
            // Récuperer le $countryCode de l'adresse de livraison de la commande
            if (version_compare(Mage::getVersion(), '1.3.0', '>')) 
            {
                $billingId = $order->getBillingAddress()->getId();
                $address = Mage::getModel('sales/order_address')->load($billingId);
            }
            else
                $address = $order->getBillingAddress();
        }
        else
        {
             //$customer = mage::getModel("customer/customer")->load(155);
             $addressId = $customer->getDefaultBilling();
             $address = mage::getModel("customer/address")->load($addressId);
        }
      
        return $address;

    }

    protected function _initWebsiteCode($customer, $order=null)
    {
        $websiteCode = Mage::app()->getWebsite($customer->getWebsiteId());

        if($order)
        {
            $websiteCode = $order->getStore()->getWebsite()->getCode();
        }
        return $websiteCode;
    }


    /**
    *
    * Export Customer
    *
    **/
    public function addCustomer($customer, $order = null)
    {
        $address = $this->_initAddress($customer, $order);
        $websiteCode = $this->_initWebsiteCode($customer, $order);
        // add Customer SAP ByD
        $sapbydCustomer = new Dvl_Sapbyd_Model_Soap_Customer('manage');
        $sapbydCustomer->setModelData($customer, $websiteCode, $address->getCountryId());
        $result = $sapbydCustomer->request();
        if(!empty($result->InternalID))
        {
            $sapbydCustomerId = $result->InternalID;
            $customerId = $customer->getId();
            //Message for email
            $message = "NEW CUSTOMER" . chr(13);
            $message .= "------------------------" . chr(13);
            $message .= $customer->getFirstname() . ' ' .$customer->getLastname() . chr(13);
            $message .= "------------------------" . chr(13);
            $message .= "Magento Customer ID : " . $customer->getIncrementId() . chr(13);
            $message .= "SAP Customer ID : " . $sapbydCustomerId . chr(13);

            $this->log($message);
            
            $sapData = array(
                'sapbyd_customer_id'   => $sapbydCustomerId,
                'sapbyd_code'                 => 'NEW',
                'sapbyd_message'              => $message,
            );


            $customer ->setData(array_merge($customer->getData(), $sapData));
            $customer->save();

            return $result->InternalID;
        }else{
            $result = new SimpleXMLElement($result);
            $message = array();
            $errors = $result->xpath('//Item');
            foreach($errors as $error)
            {
                $message [] = $error->Note->__toString();
            }

            $sapData = array(
                'sapbyd_code'                 => 'ERROR',
                'sapbyd_message'              => implode("\n",$message),
            );

            $this->log(implode("\n",$message), Zend_Log::ERR);
            Mage::getSingleton('core/session')->addError(implode("\n",$message));
            $customer ->setData(array_merge($customer->getData(), $sapData));
            $customer->save();

            return null;
        }
    }

    public function log($message, $level = Zend_Log::DEBUG )
    {
        Mage::log($message, $level, 'sapbyd.log');
    }
}