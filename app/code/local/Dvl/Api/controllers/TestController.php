<?php

/**
 * Odura
 *
 * @category    Dvl
 * @package     Dvl_Api
 * @copyright   
 * @license     
 */

/**
 * auth authorize controller
 *
 * @category Dvl
 * @package Dvl_Api
 * @author Odura
 */
class Dvl_Api_TestController extends Mage_Core_Controller_Front_Action {
    
    protected $myrequest = "
         <Customer>
            <CategoryCode>1</CategoryCode> <!-- VAleur Fixe: 1-client particulier-->
            <CustomerIndicator>true</CustomerIndicator><!--Valeur Fixe:true -->
            <Person>
               <GivenName>TEST001</GivenName><!-- Prénom du client-->
               <FamilyName>Prenom</FamilyName>
<!-- Nom du client-->
               <GenderCode>1</GenderCode>
<!--1-Homme 2-Femme -->
               <NonVerbalCommunicationLanguageCode>FR</NonVerbalCommunicationLanguageCode>
 <!-- Code Langue -->
            </Person>
            <LifeCycleStatusCode>2</LifeCycleStatusCode><!-- 2: Actif, nécessaire pour maintenir les données de paiement-->

            <SalesArrangement>
               <SalesOrganisationID>Ecommerce_FR</SalesOrganisationID><!--Ecommerce_FR pour client FRance, Ecommerce_US pour filialle US -->
               <DistributionChannelCode>Z1</DistributionChannelCode><!--Z1: Internet -->
               <DeliveryPriorityCode>2</DeliveryPriorityCode> <!-- Priorité Haute-->
               <CompleteDeliveryRequestedIndicator>true</CompleteDeliveryRequestedIndicator> <!-- true, livraison complète obligatoire-->
               <CurrencyCode>EUR</CurrencyCode><!--Devise du client -->
               <CustomerGroupCode>Z9</CustomerGroupCode> <!-- Z9 - client internet-->
               <CashDiscountTermsCode>1001</CashDiscountTermsCode><!-- 1001 - Paiement immédiat -->
            </SalesArrangement>
           
            <PaymentData> <!-- Seulement pour les client France, pas nécessaire pour les clients filialle-->
               <CompanyID>DEVIALET</CompanyID> <!--DEVIALET -->
               <AccountDeterminationDebtorGroupCode>4010</AccountDeterminationDebtorGroupCode> <!--4010 - Client National -->
           </PaymentData>
            <DuplicateCheckApplyIndicator>false</DuplicateCheckApplyIndicator> <!-- False. Pas de contrôle des doublons -->
         </Customer>        
        ";
    protected $classmap = array(
        "BasicMessageHeader" => "",
        "Customer" => array(
            "CategoryCode" => 1,
            "CustomerIndicator" => ""
        ) 
    );

    protected $classmap2 = array(
        "Customer" => ""
    );
    
	public function indexAction(){
	    $login = "_M1";
	    $pw = "Welcome1";
	    $classmap = new Dvl_Sapbyd_Model_Soap_Classmap_Customer();
        $soap = new Dvl_Sapbyd_Model_Soap_Sapbyd(Mage::getConfig()->getModuleDir('etc', "Dvl_Api").DS.'customer.wsdl');
        
        $soap->setHttpLogin($login);
        $soap->setHttpPassword($pw);
        $soap->setClassmap($this->classmap2);
        
        //$result = $soap->MaintainBundle_V1($this->myrequest);
        
        
        var_dump($soap->getHttpsCertificate());
        die();
	    echo($soap->getSoapVersion());
	}

}
