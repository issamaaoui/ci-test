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

//require_once 'Customweb/Payment/Authorization/Recurring/IAdapter.php';
//require_once 'Customweb/Paybox/Authorization/AbstractAdapter.php';
//require_once 'Customweb/Paybox/Authorization/Transaction.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Paybox/Authorization/Recurring/ParameterBuilder.php';



/**
 *
 * @author Thomas Brenner
 *         @Bean
 *        
 */
class Customweb_Paybox_Authorization_Recurring_Adapter extends Customweb_Paybox_Authorization_AbstractAdapter implements Customweb_Payment_Authorization_Recurring_IAdapter {

	public function isAuthorizationMethodSupported(Customweb_Payment_Authorization_IOrderContext $orderContext) {
		return true;
	}

	public function getAuthorizationMethodName() {
		return self::AUTHORIZATION_METHOD_NAME;
	}

	public function getAdapterPriority() {
		return 300;
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

	/**
	 * This method creates a new recurring transaction.
	 *
	 * @param Customweb_Payment_Recurring_ITransactionContext $transactionContext        	
	 */
	public function createTransaction(Customweb_Payment_Authorization_Recurring_ITransactionContext $transactionContext) {
		$transaction = new Customweb_Paybox_Authorization_Transaction ( $transactionContext );
		$transaction->setAuthorizationMethod ( self::AUTHORIZATION_METHOD_NAME );
		$transaction->createRoutingUrls ( $this->getContainer () );
		$transaction->setLiveTransaction($this->getConfiguration()->isLiveMode());
		$transaction->setPaymentId($transaction->getExternalTransactionId());
		return $transaction;
	}

	/**
	 * This method debits the given recurring transaction on the customers card.
	 *
	 * @param Customweb_Payment_Authorization_ITransaction $transaction        	
	 * @throws If something goes wrong
	 * @return void
	 */
	public function process(Customweb_Payment_Authorization_ITransaction $transaction) {
		$oldTransaction = $transaction->getTransactionContext ()
			->getInitialTransaction ();
		$parameterBuilder = new Customweb_Paybox_Authorization_Recurring_ParameterBuilder ( $transaction, $this->getConfiguration () );
		$results = $this->convertResult ( $this->sendRequest ( $this->getConfiguration ()
			->getPayboxServerUrl (), $parameterBuilder->buildRecurringParameters ( $oldTransaction ) ) );
		try {
			if ($results ['CODEREPONSE'] != "00000") {
				throw new Exception ( 
						Customweb_I18n_Translation::__ ( "The recurring transaction could not be conductet with error code: !code, and reason: !reason: ", 
								array (
									'!code' => $results ['CODEREPONSE'],
									'!reason' => $results ['COMMENTAIRE'] 
								) ) );
			}
			$transaction->authorize ();
			$paymentType = $parameterBuilder->getPaymentType ();
			if ($paymentType ['TYPE'] == "00003") {
				$transaction->capture ();
			}
			$transaction->setNumParameters($results);
			
		} catch ( Customweb_Paybox_Exception_PaymentErrorException $e ) {
			$transaction->setAuthorizationFailed ( $e->getErrorMessage () );
		} catch ( Exception $e ) {
			$transaction->setAuthorizationFailed ( $e->getMessage () );
		}
	}
}