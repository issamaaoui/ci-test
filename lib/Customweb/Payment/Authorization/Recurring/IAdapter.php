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

//require_once 'Customweb/Payment/Authorization/IAdapter.php';


interface Customweb_Payment_Authorization_Recurring_IAdapter extends Customweb_Payment_Authorization_IAdapter{

	const AUTHORIZATION_METHOD_NAME = 'Recurring';

	/**
	 * This method returns true, when the given payment method supports recurring payments.
	 *
	 * @param Customweb_Payment_Authorization_IPaymentMethod $paymentMethod
	 * @return boolean
	 */
	public function isPaymentMethodSupportingRecurring(Customweb_Payment_Authorization_IPaymentMethod $paymentMethod);

	/**
	 * This method creates a new recurring transaction.
	 *
	 * @param Customweb_Payment_Recurring_ITransactionContext $transactionContext
	 */
	public function createTransaction(Customweb_Payment_Authorization_Recurring_ITransactionContext $transactionContext);

	/**
	 * This method debits the given recurring transaction on the customers card.
	 *
	 * @param Customweb_Payment_Authorization_ITransaction $transaction
	 * @throws If something goes wrong
	 * @return void
	 */
	public function process(Customweb_Payment_Authorization_ITransaction $transaction);

}
