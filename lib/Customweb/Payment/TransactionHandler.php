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

//require_once 'Customweb/Payment/Entity/AbstractTransaction.php';
//require_once 'Customweb/Payment/ITransactionHandler.php';


/**
 * @author Thomas Hunziker
 * @Bean
 */
class Customweb_Payment_TransactionHandler implements Customweb_Payment_ITransactionHandler{
	
	/**
	 * @var Customweb_Database_Entity_IManager
	 */
	private $manager = null;
	
	/**
	 * @var Customweb_Database_IDriver
	 */
	private $driver = null;
	
	/**
	 * @var string
	 */
	private $transactionClassName = null;
	
	/**
	 * 
	 * @Inject({'Customweb_Database_Entity_IManager', 'databaseTransactionClassName', 'Customweb_Database_IDriver'})
	 */
	public function __construct(Customweb_Database_Entity_IManager $manager, $transactionClassName, Customweb_Database_IDriver $driver) {
		$this->manager = $manager;
		$this->transactionClassName = $transactionClassName;
		$this->driver = $driver;
	}
	
	public function isTransactionRunning() {
		return $this->getDriver()->isTransactionRunning();
	}
		
	public function beginTransaction() {
		return $this->getDriver()->beginTransaction();
	}

	public function commitTransaction() {
		return $this->getDriver()->commit();
	}

	public function rollbackTransaction() {
		return $this->getDriver()->rollBack();
	}

	public function findTransactionByTransactionExternalId($transactionId) {
		return $this->findTransactionEntityByTransactionExternalId($transactionId)->getTransactionObject();
	}
	
	/**
	 * @param string $transactionId
	 * @throws Exception
	 * @return Customweb_Payment_Entity_AbstractTransaction
	 */
	protected function findTransactionEntityByTransactionExternalId($transactionId) {
		$transactions = $this->getManager()->searchByFilterName($this->getTransactionClassName(), 'loadByExternalId', array('>transactionExternalId' => $transactionId));
		if (count($transactions) !== 1) {
			throw new Exception("Transaction could not be loaded by the external transaction id.");
		}
		$transaction = end($transactions);
		if (!($transaction instanceof Customweb_Payment_Entity_AbstractTransaction)) {
			throw new Exception("Transaction must be of type Customweb_Payment_Entity_AbstractTransaction");
		}
		return $transaction;
	}
	
	public function findTransactionByPaymentId($paymentId) {
		$transactions = $this->getManager()->searchByFilterName($this->getTransactionClassName(), 'loadByPaymentId', array('>paymentId' => $paymentId));
		if (count($transactions) !== 1) {
			throw new Exception("Transaction could not be loaded by the payment id.");
		}
		$transaction = end($transactions);
		if (!($transaction instanceof Customweb_Payment_Entity_AbstractTransaction)) {
			throw new Exception("Transaction must be of type Customweb_Payment_Entity_AbstractTransaction");
		}
		return $transaction->getTransactionObject();
	}

	public function findTransactionByTransactionId($transactionId) {
		$transaction = $this->getManager()->fetch($this->getTransactionClassName(), $transactionId);
		if (!($transaction instanceof Customweb_Payment_Entity_AbstractTransaction)) {
			throw new Exception("Transaction must be of type Customweb_Payment_Entity_AbstractTransaction");
		}
		return $transaction->getTransactionObject();
	}
	

	public function findTransactionsByOrderId($orderId) {
		$transactions = $this->getManager()->searchByFilterName($this->getTransactionClassName(), 'loadByOrderId', array('>orderId' => $orderId));
		$rs = array();
		foreach ($transactions as $transaction) {
			if (!($transaction instanceof Customweb_Payment_Entity_AbstractTransaction)) {
				throw new Exception("Transaction must be of type Customweb_Payment_Entity_AbstractTransaction");
			}
			if ($transaction->getTransactionObject() !== null) {
				$rs[$transaction->getTransactionId()] = $transaction->getTransactionObject();
			}
		}
		
		return $rs;
	}
	

	public function persistTransactionObject(Customweb_Payment_Authorization_ITransaction $transaction) {
		$transaction = $this->findTransactionEntityByTransactionExternalId($transaction->getExternalTransactionId())->setTransactionObject($transaction);
		$this->getManager()->persist($transaction);
	}

	protected function getManager(){
		return $this->manager;
	}

	protected function getDriver(){
		return $this->driver;
	}

	protected function getTransactionClassName(){
		return $this->transactionClassName;
	}
	


}