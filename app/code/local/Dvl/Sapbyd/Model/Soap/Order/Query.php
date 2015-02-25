<?php 

class Dvl_Sapbyd_Model_Soap_Order_Query extends Dvl_Sapbyd_Model_Soap
{
    /**
     * Model name for request
     *
     * @var string
     */
    var $modelName = "order"; // default
    
    /**
     * Model type for request
     *
     * @var string
     */
    var $modelType = "query"; //default

    /**
     * DataItems xml
     *
     * @var Zend_Config_Xml
     */
    var $dataItems = array();
    
    public function __construct($modelType = NULL){
        
        if(!empty($modelType)){
            $this->modelType = $modelType;
        }
        parent::__construct();
    }
    
    public function searchByMageID($orderId){
        $this->modelType = "query";
        $this->modelData->SalesOrderSelectionByElements->SelectionByID->LowerBoundaryInternalID = $orderId;
        return $this->request();
    }
    
    public function searchByInternalID($id){
        $this->modelType = "query";
        $this->modelData->SalesOrderSelectionByElements->SelectionByID->LowerBoundaryInternalID = $id;
        return $this->request();
    }
    

    public function afterRequest($response, $request = null){
        if(empty($response->ID)){
            $mageSapbydLog = Mage::getModel('dvl_sapbyd/log');
            $mageSapbydLog->addData(array(
                'dvl_sapbyd_customer_id'    => 0,
                'dvl_sapbyd_order_id'       => 0,
                'type'                      => $this->modelName,
                'status'                    => 'error',
                'response'                  => print_r($response, true),
                'message'                   => $request,
                'created_on'                => $mageSapbydLog->getResource()->formatDate(time()),
                'updated_on'                => $mageSapbydLog->getResource()->formatDate(time()),
            ));
            $mageSapbydLog->saveLog();
        }
        return $response;
    }
    
    public function afterRenderRequest($xmlRequest = ""){
        
        return $xmlRequest;
    }
    
    public function setModelData($sapbydCustomerId, $customer, $payment, $transaction){
        $order = $payment->getOrder();
        $this->order = $order;
        $orderId = $order->getId();
        $orderItems = $order->getItemsCollection();
        $orderBillingAddress = $order->getBillingAddress();
        $orderShippingAddress = $order->getShippingAddress();
        $customerId = $order->getCustomerId();

        $websiteCode = $order->getStore()->getWebsite()->getCode();

        // BuyerId
        $this->modelData->SalesOrder->BuyerID = $order->getIncrementId();
        
        // PostingDate
        $this->modelData->SalesOrder->PostingDate = date(DATE_ATOM);
        
        // DataOriginTypeCode
        $this->modelData->SalesOrder->DataOriginTypeCode = $this->mainConfig->website->$websiteCode->DataOriginTypeCode;
        
        // SalesAndServiceBusinessArea
        $this->modelData->SalesOrder->SalesAndServiceBusinessArea->DistributionChannelCode = $this->mainConfig->website->$websiteCode->DistributionChannelCode;
        
        // BillToParty
        // @TODO mettre à jour les adresses de magasin => éventuellement dans le fichier de config SAP
        if ($this->mainConfig->website->$websiteCode->sap_branch == 1){ // branch case 
            $this->modelData->SalesOrder->BillToParty->PartyID = $this->mainConfig->website->$websiteCode->PartyID;
            unset($this->modelData->SalesOrder->BillToParty->Address);

            // AccountParty -- 
            $this->modelData->SalesOrder->AccountParty->PartyID = $this->mainConfig->website->$websiteCode->PartyID;
            unset($this->modelData->SalesOrder->AccountParty->Address);

        }else{ // main company case
            $this->modelData->SalesOrder->BillToParty->PartyID = $sapbydCustomerId;
            $this->modelData->SalesOrder->BillToParty->Address->Email->URI = $order->getCustomerEmail();
            $this->modelData->SalesOrder->BillToParty->Address->Telephone->FormattedNumberDescription = $orderBillingAddress->getTelephone(); //pas phone number
            $this->modelData->SalesOrder->BillToParty->Address->PostalAddress->CountryCode = $orderBillingAddress->getCountry();
            $this->modelData->SalesOrder->BillToParty->Address->PostalAddress->CityName = $orderBillingAddress->getCity();
            $this->modelData->SalesOrder->BillToParty->Address->PostalAddress->StreetPostalCode = $orderBillingAddress->getPostcode();
            $this->modelData->SalesOrder->BillToParty->Address->PostalAddress->StreetName = $orderBillingAddress->getStreet1();
            $this->modelData->SalesOrder->BillToParty->Address->DisplayName->FormattedName = $customer->getFirstname() . ' ' . $customer->getLastname();
            
            // AccountParty --
            $this->modelData->SalesOrder->AccountParty->PartyID = $sapbydCustomerId;
            $this->modelData->SalesOrder->AccountParty->Address->Email->URI = $order->getCustomerEmail();
            $this->modelData->SalesOrder->AccountParty->Address->Telephone->FormattedNumberDescription = $orderBillingAddress->getTelephone(); //pas phone number
            $this->modelData->SalesOrder->AccountParty->Address->PostalAddress->CountryCode = $orderBillingAddress->getCountry();
            $this->modelData->SalesOrder->AccountParty->Address->PostalAddress->CityName = $orderBillingAddress->getCity();
            $this->modelData->SalesOrder->AccountParty->Address->PostalAddress->StreetPostalCode = $orderBillingAddress->getPostcode();
            $this->modelData->SalesOrder->AccountParty->Address->PostalAddress->StreetName = $orderBillingAddress->getStreet1();
            $this->modelData->SalesOrder->AccountParty->Address->PostalAddress->StreetName2 = $orderBillingAddress->getStreet2();
            $this->modelData->SalesOrder->AccountParty->Address->PostalAddress->Company = $orderBillingAddress->getCompany();
            $this->modelData->SalesOrder->AccountParty->Address->DisplayName->FormattedName = $customer->getFirstname() . ' ' . $customer->getLastname();
        }
        // ProductRecipientParty -- always customer
        $this->modelData->SalesOrder->ProductRecipientParty->PartyID = $sapbydCustomerId;
        $this->modelData->SalesOrder->ProductRecipientParty->Address->Email->URI = $order->getCustomerEmail();
        $this->modelData->SalesOrder->ProductRecipientParty->Address->Telephone->FormattedNumberDescription = $orderShippingAddress->getTelephone(); //pas phone number
        $this->modelData->SalesOrder->ProductRecipientParty->Address->PostalAddress->CountryCode = $orderShippingAddress->getCountry();
        $this->modelData->SalesOrder->ProductRecipientParty->Address->PostalAddress->CityName = $orderShippingAddress->getCity();
        $this->modelData->SalesOrder->ProductRecipientParty->Address->PostalAddress->StreetPostalCode = $orderShippingAddress->getPostcode();
        $this->modelData->SalesOrder->ProductRecipientParty->Address->PostalAddress->StreetName = $orderShippingAddress->getStreet1();
        $this->modelData->SalesOrder->ProductRecipientParty->Address->PostalAddress->StreetName2 = $orderShippingAddress->getStreet2();
        $this->modelData->SalesOrder->ProductRecipientParty->Address->PostalAddress->Company = $orderShippingAddress->getCompany();
        $this->modelData->SalesOrder->ProductRecipientParty->Address->DisplayName->FormattedName = $customer->getFirstname() . ' ' . $customer->getLastname();
        
        // EmployeeResponsibleParty
        $this->modelData->SalesOrder->EmployeeResponsibleParty->PartyID = $this->mainConfig->website->$websiteCode->EmployeeResponsibleParty;
        
        // SellerParty
        $this->modelData->SalesOrder->SellerParty->PartyID = $this->mainConfig->website->$websiteCode->SellerParty;
        
        // SalesUnitParty
        $this->modelData->SalesOrder->SalesUnitParty->PartyID = $this->mainConfig->website->$websiteCode->sap_store;
        
        // DeliveryTerms
        $this->modelData->SalesOrder->DeliveryTerms->DeliveryPriorityCode = $this->mainConfig->website->$websiteCode->DeliveryPriorityCode;
        $this->modelData->SalesOrder->DeliveryTerms->Incoterms->ClassificationCode = $this->mainConfig->website->$websiteCode->ClassificationCode;
        $this->modelData->SalesOrder->DeliveryTerms->Incoterms->TransferLocationName = $orderShippingAddress->getCity();
        
        // CashDiscountTerms
        $this->modelData->SalesOrder->CashDiscountTerms->Code = $this->mainConfig->website->$websiteCode->CashDiscountTerms;
        
        // PricingTerms
        $this->modelData->SalesOrder->PricingTerms->CurrencyCode = $this->mainConfig->website->$websiteCode->sap_currency;
        $this->modelData->SalesOrder->PricingTerms->GrossAmountIndicator = $this->mainConfig->website->$websiteCode->GrossAmountIndicator;
        
        foreach ($orderItems as $item){
            $product = $item->getProduct();
            $this->addItem($product->getDvlProductId(), $item->getQtyOrdered());
        }
        
        if ($this->mainConfig->website->$websiteCode->sap_branch == 1){
            unset($this->modelData->SalesOrder->PaymentControl);
        }else{
            // PaymentControl
            $this->modelData->SalesOrder->PaymentControl->PaymentProcessingCompanyID = "Devialet";
            $this->modelData->SalesOrder->PaymentControl->PaymentReferenceID = $payment->getId();
            $this->modelData->SalesOrder->PaymentControl->PaymentReferenceTypeCode = "5";
            $this->modelData->SalesOrder->PaymentControl->PropertyMovementDirectionCode = "2";
            $this->modelData->SalesOrder->PaymentControl->PaymentFormCode = "20";
            $this->modelData->SalesOrder->PaymentControl->PaymentAmount = $transaction->getAuthorizationAmount();
            
            // PaymentControl ExternalPayment
            $this->modelData->SalesOrder->PaymentControl->ExternalPayment->HouseBankAccountKeyInternalID = $this->mainConfig->website->$websiteCode->HouseBankAccountKeyInternalID;
            $this->modelData->SalesOrder->PaymentControl->ExternalPayment->PaymentTransactionReferenceID = $payment->getLastTransId();
            $this->modelData->SalesOrder->PaymentControl->ExternalPayment->DocumentDate = date('Y-m-d');
            $this->modelData->SalesOrder->PaymentControl->ExternalPayment->ValueDate = date('Y-m-d');
            $this->modelData->SalesOrder->PaymentControl->ExternalPayment->Amount = $transaction->getAuthorizationAmount();
        }

        return $this;
    }
    
    public function addItem($productId, $quantity){
        if(!empty($productId)){
            $itemNb = 10 * (count($this->dataItems) + 1);
            $itemXml = "
            <Item>
            <ID>$itemNb</ID>
            <BuyerID>$itemNb</BuyerID>
            <ItemProduct>
            <ProductID>$productId</ProductID>
            </ItemProduct>
            <ItemScheduleLine>
            <Quantity unitCode='EA'>$quantity</Quantity>
            </ItemScheduleLine>
            </Item>
            ";
            $this->dataItems[] = $itemXml;
        }
        
        // @TODO
        //<FormattedName languageCode="FR">
        //<Quantity unitCode='EA'>
        //return $this->request();
    }
    

}


?>