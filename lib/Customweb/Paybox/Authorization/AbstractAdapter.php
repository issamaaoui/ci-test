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

//require_once 'Customweb/Core/Util/Class.php';
//require_once 'Customweb/Core/Http/Response.php';
//require_once 'Customweb/Paybox/Authorization/Server/ParameterBuilder.php';
//require_once 'Customweb/Payment/Authorization/IInvoiceItem.php';
//require_once 'Customweb/Paybox/AbstractAdapter.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Paybox/Method/Factory.php';


/**
 *
 * @author Thomas Brenner
 *
 */
class Customweb_Paybox_Authorization_AbstractAdapter extends Customweb_Paybox_AbstractAdapter{
	
	public function isDeferredCapturingSupported(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext) {
		// TODO: Move this into a dedicated authorization adpater
		return $orderContext->getPaymentMethod()->existsPaymentMethodConfigurationValue('capturing');
	}
	
	public function getTotalAmountOfLineItems(Customweb_Payment_Authorization_ITransaction $transaction) {
		$totalAmount = 0;
		$parameters = array();
		foreach ($transaction->getTransactionContext()->getOrderContext()->getInvoiceItems() as $item) {
	
			if ($item->getType() == Customweb_Payment_Authorization_IInvoiceItem::TYPE_DISCOUNT) {
				$totalAmount -= $item->getAmountIncludingTax();
			}
			else {
				$totalAmount += $item->getAmountIncludingTax();
			}
		}
		return number_format((float)$totalAmount, 2, '.', '');
	}
	
	/**
	 * 
	 * @param Customweb_Payment_Authorization_Server_ITransactionContext $transactionContext
	 * @throws Exception
	 */
	protected function checkRecurring(Customweb_Payment_Authorization_Server_ITransactionContext $transactionContext) {
		if ($transactionContext->createRecurringAlias()) {
			if (!Customweb_Paybox_Method_Factory::getMethod($transactionContext->getOrderContext()->getPaymentMethod(), $this->getConfiguration())->isRecurringPaymentSupported()) {
				throw new Exception(Customweb_I18n_Translation::__("The payment method !paymentMethod does not support recurring payment.",
						array('!paymentMethod' => $transactionContext->getOrderContext()->getPaymentMethod()->getPaymentMethodName())
				));
			}
		}
	}
	
	protected function verifySign(array $parameters) {
		switch (strlen($parameters['sign'])) {
			case 172 :
				$signature = base64_decode($parameters['sign']);
				break;
			case 128 :
				$signature = $parameters['sign'];
				break;
			default :
				throw new Exception(Customweb_I18n_Translation::__("Bad signature format."));
				break;
		}
		
		$params = 
			"reference="  	. $parameters['reference'] .
			"&error="		. $parameters['error'] .
			"&transaction="	. $parameters['transaction'] .
			"&appel="		. $parameters['appel'] .
			"&amount="		. $parameters['amount'] . 
			"&cardtype="	. $parameters['cardtype'] .
			"&cardStart="	. $parameters['cardStart'] . 
			"&cardEnd="	.	 $parameters['cardEnd'] .
			"&aliasInfo="	. urlencode($parameters['aliasInfo']);
		
		$pubkey = Customweb_Core_Util_Class::readResource("Customweb/_Paybox_/Authorization", "pubkey.pem");
		$ok = openssl_verify($params, $signature, $pubkey);
		if ($ok ) {
			return;
		} else {
			throw new Exception(Customweb_I18n_Translation::__("The signature could not be verified."));
		}
	}
	
	protected function verifyParameters($transaction, array $parameters) {
		$amount = 100 * $transaction->getTransactionContext()->getOrderContext()->getOrderAmountInDecimals();
		if($parameters['amount'] != $amount) {
			throw new Exception(Customweb_I18n_Translation::__("The amount could not be verified."));
		}
		
		if($parameters['error'] != "00000") {
			throw new Exception(Customweb_I18n_Translation::__("The transaction failed with error code !code.", array('!code' => $parameters['error'])));
		}
	}

	public function performAliasTransaction(Customweb_Paybox_Authorization_Transaction $transaction, array $parameters) {
		$parameterBuilder = new Customweb_Paybox_Authorization_Server_ParameterBuilder($transaction, $this->getConfiguration());
		$oldTransaction = $transaction->getTransactionContext()->getAlias();
		$results = $this->convertResult($this->sendRequest(
				$this->getConfiguration()->getPayboxServerUrl(),
				$parameterBuilder->buildAliasTransactionParameters($oldTransaction, $parameters['CVV'])));
		try {
			if($results['CODEREPONSE'] != "00000") {
				throw new Exception(Customweb_I18n_Translation::__("The alias transaction could not be conductet with error code: !code, and reason: !reason: ",
						array('!code' => $results['CODEREPONSE'], '!reason' => $results['COMMENTAIRE'])));
			}
			$transaction->authorize();
			$paymentType = $parameterBuilder->getPaymentType();
			if($paymentType['TYPE'] == "00003") {
				$transaction->capture();
			}
		}
		catch(Customweb_Paybox_Exception_PaymentErrorException $e) {
			$transaction->setAuthorizationFailed($e->getErrorMessage());
		}
		catch(Exception $e) {
			$transaction->setAuthorizationFailed($e->getMessage());
		}
		$transaction->setStatus("FINISH");
		
		
		$transaction->setNumParametersPaymentPage($results['NUMTRANS'], $results['NUMAPPEL']);
		
		$response = new Customweb_Core_Http_Response();
		if($transaction->isAuthorizationFailed()) {
			return $response->appendHeader("Location: " . $transaction->getFailedUrl());
		} else {
			return $response->appendHeader("Location: " . $transaction->getSuccessUrl());
		}
	}
}