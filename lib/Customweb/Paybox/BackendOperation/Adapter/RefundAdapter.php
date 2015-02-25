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

//require_once 'Customweb/Payment/BackendOperation/Adapter/Service/IRefund.php';
//require_once 'Customweb/Util/Invoice.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Paybox/AbstractParameterBuilder.php';
//require_once 'Customweb/Paybox/BackendOperation/Adapter/AbstractAdapter.php';


/**
 *
 * @author Thomas Brenner
 * @Bean
 *
 */
class Customweb_Paybox_BackendOperation_Adapter_RefundAdapter extends Customweb_Paybox_BackendOperation_Adapter_AbstractAdapter
	implements Customweb_Payment_BackendOperation_Adapter_Service_IRefund {

	public function refund(Customweb_Payment_Authorization_ITransaction $transaction){
		$items = $transaction->getTransactionContext()->getOrderContext()->getInvoiceItems();
		return partialRefund($transaction, $items, true);
	}

	public function partialRefund(Customweb_Payment_Authorization_ITransaction $transaction, $items, $close){
		
		$amount = Customweb_Util_Invoice::getTotalAmountIncludingTax($items);
		
		if($this->doRefund($transaction, $items, $amount, $close)) {
			$transaction->refund($amount);
		} else {
			throw new Exception(Customweb_I18n_Translation::__("The refunding could not be conducted."));
		}
	}
	
	private function doRefund(Customweb_Payment_Authorization_ITransaction $transaction, $items, $amount, $close) {
		$adjustedAmount = 100 * $amount;
		$parameterBuilder = new Customweb_Paybox_AbstractParameterBuilder($transaction, $this->getConfiguration());
		$transaction->refundByLineItemsDry($items, $close);
		
		$results = $this->convertResult($this->sendRequest($this->getConfiguration()->getPayboxServerUrl(), $parameterBuilder->buildRefundParameters($adjustedAmount)));
		
		if ($results['CODEREPONSE'] == '00000') {
			return true;
		}
		else {
			return false;
		}
		
	}

}