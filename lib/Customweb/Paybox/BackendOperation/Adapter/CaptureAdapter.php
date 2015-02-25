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

//require_once 'Customweb/Payment/BackendOperation/Adapter/Service/ICapture.php';
//require_once 'Customweb/Util/Invoice.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Paybox/AbstractParameterBuilder.php';
//require_once 'Customweb/Paybox/BackendOperation/Adapter/AbstractAdapter.php';


//require_once 'Customweb/Paybox/Authorization/Transaction.php';

/**
 *
 * @author Thomas Brenner
 * @Bean
 *
 */
class Customweb_Paybox_BackendOperation_Adapter_CaptureAdapter extends Customweb_Paybox_BackendOperation_Adapter_AbstractAdapter
implements Customweb_Payment_BackendOperation_Adapter_Service_ICapture{

	public function capture(Customweb_Payment_Authorization_ITransaction $transaction){
		/* @var $transaction Customweb_Paybox_Authorization_Transaction */
		$items = $transaction->getUncapturedLineItems();
		$this->partialCapture($transaction, $items, true);
	}

	public function partialCapture(Customweb_Payment_Authorization_ITransaction $transaction, $items, $close){
		$amount = Customweb_Util_Invoice::getTotalAmountIncludingTax($items);
		try {
			$this->doCapturing($transaction, $amount);
			$transaction->partialCaptureByLineItems($items, $close);
			if($this->doCapturing($transaction, $amount)) {
				
			} else {
				throw new Exception(Customweb_I18n_Translation::__("The capturing could not be conducted."));
			}
		}
		catch(Exception $e) {
			$transaction->addErrorMessage($e->getMessage());
		}
	}
	
	private function doCapturing(Customweb_Payment_Authorization_ITransaction $transaction,$amount) {
		$adjustedAmount = 100*$amount;
		
		$parameterBuilder = new Customweb_Paybox_AbstractParameterBuilder($transaction, $this->getConfiguration());
		
		$results = $this->convertResult($this->sendRequest($this->getConfiguration()->getPayboxServerUrl(), $parameterBuilder->buildCaptureParameters($adjustedAmount)));
		
		if ($results['CODEREPONSE'] == '00000') {
			return;
		}
		else {
			$error = $results['CODEREPONSE'];
			throw new Exception(Customweb_I18n_Translation::__("The capturing could not be conducted with error code: $error."));
		}
	}
		
}