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

//require_once 'Customweb/Payment/BackendOperation/Adapter/Service/ICancel.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Paybox/AbstractParameterBuilder.php';
//require_once 'Customweb/Paybox/BackendOperation/Adapter/AbstractAdapter.php';


/**
 *
 * @author Thomas Brenner
 * @Bean
 *
 */
class Customweb_Paybox_BackendOperation_Adapter_CancellationAdapter extends Customweb_Paybox_BackendOperation_Adapter_AbstractAdapter
 implements Customweb_Payment_BackendOperation_Adapter_Service_ICancel {


	public function cancel(Customweb_Payment_Authorization_ITransaction $transaction) {
		$transaction->cancelDry();
		
		try {
			if($this->doCancellation($transaction)) {
				$transaction->cancel();
			} else {
				throw new Exception(Customweb_I18n_Translation::__("The cancellation could not be conducted."));
			}
		}
		catch(Exception $e) {
			$transaction->addErrorMessage($e->getMessage());
		}
	}
	
	protected function doCancellation(Customweb_Payment_Authorization_ITransaction $transaction) {
		$parameterBuilder = new Customweb_Paybox_AbstractParameterBuilder($transaction, $this->getConfiguration());
		
		$results = $this->convertResult($this->sendRequest($this->getConfiguration()->getPayboxServerUrl(), $parameterBuilder->buildCancellationParameters()));
		
		if ($results['CODEREPONSE'] == '00000') {
			return true;
		}
		else {
			return false;
		}
		
	}
			
}