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
 *
 * @category	Customweb
 * @package		Customweb_PayboxCw
 * @version		1.0.49
 */

//require_once 'Customweb/Payment/ITransactionHandler.php';


/**
 * @Bean
 */
class Customweb_PayboxCw_Model_TransactionHandler extends Mage_Core_Model_Abstract implements Customweb_Payment_ITransactionHandler
{
	protected function _construct()
	{
		$this->_init('payboxcw/transaction');
	}
	
	public function isTransactionRunning() {
		return false;
	}
	
	public function beginTransaction() {
		$this->_getResource()->beginTransaction();
	}

	public function commitTransaction() {
		$this->_getResource()->commit();
	}

	public function rollbackTransaction() {
		$this->_getResource()->rollBack();
	}

	public function findTransactionByTransactionExternalId($transactionId) {
		return $this->findTransactionEntityByTransactionExternalId($transactionId)->getTransactionObject();
	}
	
	public function findTransactionByPaymentId($paymentId) {
		$transaction = Mage::getModel('payboxcw/transaction')->load($paymentId, 'payment_id');
		if ($transaction === null || !$transaction->getId()) {
			throw new Exception("Transaction could not be loaded by the payment id.");
		}
		return $transaction->getTransactionObject();
	}

	public function findTransactionByTransactionId($transactionId) {
		$transaction = Mage::getModel('payboxcw/transaction')->load($transactionId);
		if ($transaction === null || !$transaction->getId()) {
			throw new Exception("Transaction could not be loaded by the transaction id.");
		}
		return $transaction->getTransactionObject();
	}

	public function persistTransactionObject(Customweb_Payment_Authorization_ITransaction $transaction) {
		$transaction = $this->findTransactionEntityByTransactionExternalId($transaction->getExternalTransactionId())->setTransactionObject($transaction);
		$transaction->save();
	}
	
	public function findTransactionsByOrderId($orderId) {
		// TODO: Check if the $orderId is always the same id as the transaction id.
		$transaction = Mage::getModel('payboxcw/transaction')->load($orderId);
		$rs = array();
		if ($transaction !== null || !$transaction->getId()) {
			$rs[$orderId] = $transaction->getTransactionObject();
		}
		return $rs;
	}
	
	/**
	 * @param string $transactionId
	 * @throws Exception
	 * @return Customweb_Payment_Entity_AbstractTransaction
	 */
	protected function findTransactionEntityByTransactionExternalId($transactionId) {
		$transaction = Mage::getModel('payboxcw/transaction')->load($transactionId, 'transaction_external_id');
		if ($transaction === null || !$transaction->getId()) {
			throw new Exception("Transaction could not be loaded by the external transaction id.");
		}
		return $transaction;
	}
}