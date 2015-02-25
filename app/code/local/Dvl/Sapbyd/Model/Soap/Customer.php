<?php 

class Dvl_Sapbyd_Model_Soap_Customer extends Dvl_Sapbyd_Model_Soap
{
    /**
     * Model name for request
     *
     * @var string
     */
    var $modelName = "customer"; // default
    
    /**
     * Model type for request
     *
     * @var string
     */
    var $modelType = "query"; //or manage

    public function __construct($modelType = NULL){
        if(!empty($modelType)){
            $this->modelType = $modelType;
        }
        parent::__construct();
    }
    
    public function searchByMageID($customerId){
        $this->modelType = "query";
        $this->modelData->CustomerSelectionByIdentification->SelectionByInternalID->LowerBoundaryInternalID = $customerId;
        return $this->request();
    }
    
    public function searchByInternalID($id){
        $this->modelType = "query";
        $this->modelData->CustomerSelectionByIdentification->SelectionByInternalID->LowerBoundaryInternalID = $id;
        return $this->request();
    }
    
    public function createCustomer($id){
        $this->modelType = "manage";
        return $this->request();
    }

    
    public function afterRequest($response, $request = null){
        if(empty($response->InternalID)){
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
        $xmlRequest = str_replace('<AddressInformation>', '<AddressInformation actionCode="01">', $xmlRequest);
        return $xmlRequest;
    }
    
    public function setModelData(Mage_Core_Model_Abstract $customer, $websiteCode = null, $countryCode = null){
        if(empty($websiteCode)){
            $websiteCode = Mage::app()->getStore()->getWebsite()->getCode();
        }
        
        // General
        $this->modelData->Customer->CategoryCode = $this->mainConfig->website->$websiteCode->CategoryCode->b2c; // @TODO Distinguer selon le groupe de client B2B et B2C evol 2015
        $this->modelData->Customer->CustomerIndicator = $this->mainConfig->website->$websiteCode->CustomerIndicator;
        
        // Person
        $this->modelData->Customer->Person->GivenName = $customer->getFirstname();
        $this->modelData->Customer->Person->FamilyName = $customer->getLastname();
        
        $gender = $customer->getGender();
        if($gender == 2){
            $this->modelData->Customer->Person->GenderCode = "2"; // 2 Femme
        }else{
            $this->modelData->Customer->Person->GenderCode = "1"; // 1 Homme
        }
        
        $storeCode = Mage::app()->getStore()->getCode();
        if(substr($storeCode, -2) == "fr"){
            $this->modelData->Customer->Person->NonVerbalCommunicationLanguageCode = "FR";
        }else{
            $this->modelData->Customer->Person->NonVerbalCommunicationLanguageCode = "EN";
        }

        // LifeCycleStatusCode
        $this->modelData->Customer->LifeCycleStatusCode = $this->mainConfig->website->$websiteCode->LifeCycleStatusCode;

        if ($this->mainConfig->website->$websiteCode->sap_branch == 1){
            unset($this->modelData->Customer->SalesArrangement);
            unset($this->modelData->Customer->PaymentData);
            unset($this->modelData->Customer->AddressInformation);
        }else{
            // SalesArrangement
            $this->modelData->Customer->SalesArrangement->SalesOrganisationID = $this->mainConfig->website->$websiteCode->sap_store;
            $this->modelData->Customer->SalesArrangement->DistributionChannelCode = $this->mainConfig->website->$websiteCode->DistributionChannelCode;
            $this->modelData->Customer->SalesArrangement->DeliveryPriorityCode = $this->mainConfig->website->$websiteCode->DeliveryPriorityCode;
            $this->modelData->Customer->SalesArrangement->CompleteDeliveryRequestedIndicator = $this->mainConfig->website->$websiteCode->CompleteDeliveryRequestedIndicator;
            $this->modelData->Customer->SalesArrangement->CurrencyCode = $this->mainConfig->website->$websiteCode->sap_currency;
            $this->modelData->Customer->SalesArrangement->CashDiscountTermsCode = $this->mainConfig->website->$websiteCode->CashDiscountTerms;
            
            // PaymentData
            $this->modelData->Customer->PaymentData->CompanyID = $this->mainConfig->website->$websiteCode->SellerParty;
            $this->modelData->Customer->PaymentData->AccountDeterminationDebtorGroupCode = $this->mainConfig->website->$websiteCode->AccountDeterminationDebtorGroupCode;
            
            // AddressInformation
            //$this->modelData->Customer->AddressInformation->Address->PostalAddress->CountryCode = $this->mainConfig->website->$websiteCode->CountryCode;
            if(is_null($countryCode))
                $this->modelData->Customer->AddressInformation->Address->PostalAddress->CountryCode = $this->mainConfig->website->$websiteCode->CountryCode;
            else
                $this->modelData->Customer->AddressInformation->Address->PostalAddress->CountryCode = $countryCode;
        }

        $this->modelData->Customer->DuplicateCheckApplyIndicator = $this->mainConfig->website->$websiteCode->DuplicateCheckApplyIndicator;
    }
}
?>