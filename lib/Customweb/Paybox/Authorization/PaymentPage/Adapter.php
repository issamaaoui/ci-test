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
//require_once 'Customweb/Paybox/Authorization/PaymentPage/ParameterBuilder.php';
//require_once 'Customweb/Paybox/Authorization/AbstractAdapter.php';
//require_once 'Customweb/Util/Url.php';
//require_once 'Customweb/Paybox/Authorization/Transaction.php';
//require_once 'Customweb/Payment/Authorization/PaymentPage/IAdapter.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Paybox/Method/Factory.php';


/**
 * 
 * @author Thomas Brenner
 * @Bean
 *
 */
class Customweb_Paybox_Authorization_PaymentPage_Adapter extends Customweb_Paybox_Authorization_AbstractAdapter 
	implements Customweb_Payment_Authorization_PaymentPage_IAdapter {

	public function getAuthorizationMethodName(){
		return self::AUTHORIZATION_METHOD_NAME;
	}
	
	public function getAdapterPriority() {
		return 100;
	}
	
	/**
	 * This method returns true, when the given payment method supports recurring payments.
	 *
	 * @param Customweb_Payment_Authorization_IPaymentMethod $paymentMethod
	 * @return boolean
	 */
	public function isPaymentMethodSupportingRecurring(Customweb_Payment_Authorization_IPaymentMethod $paymentMethod) {
		return true;
	}

	public function createTransaction(Customweb_Payment_Authorization_PaymentPage_ITransactionContext $transactionContext, $failedTransaction){
		$transaction = new Customweb_Paybox_Authorization_Transaction($transactionContext);
		$transaction->setAuthorizationMethod(self::AUTHORIZATION_METHOD_NAME);
		$transaction->createRoutingUrls($this->getContainer());
		$transaction->setLiveTransaction($this->getConfiguration()->isLiveMode());
		$transaction->setPaymentId($transaction->getExternalTransactionId());
		return $transaction;
	}

	public function getVisibleFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction, $failedTransaction, $customerPaymentContext){
		if($aliasTransaction !== null && $aliasTransaction !== 'new'){
			return Customweb_Paybox_Method_Factory::getMethod($orderContext->getPaymentMethod(), $this->getConfiguration())
			->getFormFields($orderContext, $aliasTransaction, $failedTransaction, self::AUTHORIZATION_METHOD_NAME, false, $customerPaymentContext);
		} else {
			return array();
		}
	}

	public function isHeaderRedirectionSupported(Customweb_Payment_Authorization_ITransaction $transaction, array $formData){
		return true;
	}
	
	public function getFormActionUrl(Customweb_Payment_Authorization_ITransaction $transaction, array $formData){
		$parameterBuilder = new Customweb_Paybox_Authorization_PaymentPage_ParameterBuilder($transaction, $this->getConfiguration());
		return Customweb_Util_Url::appendParameters($transaction->getProcessAuthorizationUrl(), $parameterBuilder->buildStandardParameters());
	}
	
	public function getParameters(Customweb_Payment_Authorization_ITransaction $transaction, array $formData){
		return array();
	}
	
	public function getRedirectionUrl(Customweb_Payment_Authorization_ITransaction $transaction, array $formData){
		/* @var $transaction Customweb_Paybox_Authorization_Transaction */
		$parameterBuilder = new Customweb_Paybox_Authorization_PaymentPage_ParameterBuilder($transaction, $this->getConfiguration());
		$returnUrl = Customweb_Util_Url::appendParameters(
				$this->getConfiguration()->getPayboxUrl(),
				$parameterBuilder->buildStandardParameters());
		if($transaction->getTransactionContext()->getAlias() == "new") {
			return $returnUrl;
		} else if ($transaction->getTransactionContext()->getAlias() != null) {
			return Customweb_Util_Url::appendParameters($transaction->getProcessAuthorizationUrl(), $formData);
		} else {
			return $returnUrl;
		}
		
	}

	public function isAuthorizationMethodSupported(Customweb_Payment_Authorization_IOrderContext $orderContext){
		return true;
	}

	public function processAuthorization(Customweb_Payment_Authorization_ITransaction $transaction, array $parameters){
		//case if shop-system only supports POST --> redirection
		if(isset($parameters['PBX_SITE'])) {
			$this->redirectWithoutPost($transaction);
		}
			/* @var $transaction Customweb_Paybox_Authorization_Transaction */
		if($transaction->getTransactionContext()->getAlias() == "new" || $transaction->getTransactionContext()->createRecurringAlias()) {
			$this->performNormalAuthorization($transaction, $parameters, true);
		} else if ($transaction->getTransactionContext()->getAlias() != null) {
			return $this->performAliasTransaction($transaction, $parameters);
		} else {
			$this->performNormalAuthorization($transaction, $parameters, false);
		}
		return " ";
		
	}
	
	private function performNormalAuthorization(Customweb_Paybox_Authorization_Transaction $transaction, $parameters, $alias) {
		$parameterBuilder = new Customweb_Paybox_Authorization_PaymentPage_ParameterBuilder($transaction, $this->getConfiguration());
		try {
 			$this->verifySign($parameters);
			$this->verifyParameters($transaction, $parameters);
			$transaction->authorize(Customweb_I18n_Translation::__('Customer sucessfully returned from the Paybox payment page.'));
			$transaction->setAuthorizationParameters($parameters);
			if ($parameterBuilder->getPaymentAction() == 'N') {
				$transaction->capture();
			}
			$transaction->setNumParametersPaymentPage($parameters['transaction'], $parameters['appel']);
			
			//If true, create alias
			if($alias) {
				$this->buildPaymentPageAlias($transaction, $this->getConfiguration(), $parameters);
			}
		}
		catch(Exception $e) {
			$transaction->setAuthorizationFailed($e->getMessage());
		}
	}
	
	private function buildPaymentPageAlias(Customweb_Paybox_Authorization_Transaction $transaction , Customweb_Paybox_Configuration $configuration, $parameters) {
		$aliasForDisplay = $parameters['cardStart'] . "XXXXXXXX" . $parameters['cardEnd'];
		$transaction->setAliasForDisplay($aliasForDisplay);
		$paymentInformation = explode("  ",$parameters['aliasInfo']);
		$transaction->setAliasToken($paymentInformation[0]);
		$transaction->setDateVal($this->modifyValidationDate($paymentInformation[1]));
	}
	
	private function modifyValidationDate($date) {
		if(strlen($date) != 4) {
			throw new Exception(Customweb_I18n_Translation::__("Error with the length of the validation date."));
		} else {
			return substr($date, 2) . substr($date, 0,2);
		}
	}
	
	private function redirectWithoutPost(Customweb_Paybox_Authorization_Transaction $transaction) {
		/* @var $transaction Customweb_Paybox_Authorization_Transaction */
		$parameterBuilder = new Customweb_Paybox_Authorization_PaymentPage_ParameterBuilder($transaction, $this->getConfiguration());
		$returnUrl = Customweb_Util_Url::appendParameters(
				$this->getConfiguration()->getPayboxUrl(),
				$parameterBuilder->buildStandardParameters());
		if($transaction->getTransactionContext()->getAlias() == "new") {
			return "redirect:".$returnUrl;
		} else if ($transaction->getTransactionContext()->getAlias() != null) {
			return "redirect:".Customweb_Util_Url::appendParameters($transaction->getProcessAuthorizationUrl(), array());
		} else {
			return "redirect:".$returnUrl;
		}
	}
	
	
}