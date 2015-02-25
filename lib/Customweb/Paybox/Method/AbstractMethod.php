<?php
/**
  * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2013 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.customweb.ch/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.customweb.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 */

//require_once 'Customweb/Payment/Authorization/AbstractPaymentMethodWrapper.php';
//require_once 'Customweb/Paybox/Method/Util.php';

 
class Customweb_Paybox_Method_AbstractMethod extends Customweb_Payment_Authorization_AbstractPaymentMethodWrapper {

	private $globalConfiguration = null;

	public function __construct(Customweb_Payment_Authorization_IPaymentMethod $paymentMethod, Customweb_Paybox_Configuration $config){
		parent::__construct($paymentMethod);
		$this->globalConfiguration = $config;
	}
	
	public function getConfiguration() {
		return $this->globalConfiguration;
	}

	/**
	 *           	   	  	 	  
	 * 
	 * @return Customweb_Paybox_Configuration
	 */
	protected function getGlobalConfiguration(){
		return $this->globalConfiguration;
	}
	

	protected function getPaymentInformationMap(){
		return Customweb_Paybox_Method_Util::getPaymentMethodMap();
	}

	public function getPaymentMethodIdentifier(Customweb_Paybox_Authorization_Transaction $transaction){
		$params = $this->getPaymentMethodParameters();
		if(isset($params['PaymentMethod'])) {
			return $params['PaymentMethod'];
		} else {
			return "BUYSTER";
		}
	}
	

	/**
	 * This method returns a list of form elements.
	 * This form elements are used to generate the user input.
	 * Sub classes may override this method to provide their own form fields.
	 *
	 * @return array List of form elements
	 */
	public function getFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction, $failedTransaction, $authorizationMethod, $isMoto, $customerPaymentContext){
		return array();
	}
	
	public function getAdditionalParameters(Customweb_Paybox_Authorization_Transaction $transaction) {
		return array();
	}
	
	public function getPaymentType() {
		return "";
	}


	
}