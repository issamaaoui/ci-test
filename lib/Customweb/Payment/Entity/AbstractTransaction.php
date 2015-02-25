<?php

/**
 *  * You are allowed to use this API in your web application.
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

//require_once 'Customweb/Payment/Authorization/DefaultTransaction.php';
//require_once 'Customweb/Payment/Authorization/ITransaction.php';
//require_once 'Customweb/Payment/Entity/AbstractPaymentCustomerContext.php';
//require_once 'Customweb/Payment/Authorization/ErrorMessage.php';
//require_once 'Customweb/I18n/Translation.php';



/**
 * @Index(columnNames = {'transactionExternalId'})
 * @Index(columnNames = {'orderId'})
 * @Index(columnNames = {'paymentId'})
 *
 * @Filter(name = 'loadByPaymentId', where = 'paymentId = >paymentId', orderBy = 'paymentId')
 * @Filter(name = 'loadByExternalId', where = 'transactionExternalId = >transactionExternalId', orderBy = 'transactionId')
 * @Filter(name = 'loadByOrderId', where = 'orderId = >orderId', orderBy = 'orderId')
 */
abstract class Customweb_Payment_Entity_AbstractTransaction {
	private $transactionId;
	
	/**
	 * Combination of transactionId and orderId
	 */
	private $transactionExternalId;
	private $orderId;
	private $aliasForDisplay;
	private $aliasActive = true;
	private $paymentMachineName;
	private $transactionObject;
	private $authorizationType;
	private $customerId;
	private $updatedOn;
	private $createdOn;
	private $paymentId;
	private $updatable = false;
	private $executeUpdateOn;
	private $authorizationAmount;
	private $currency;
	private $authorizationStatus;
	private $paid = true;
	protected $isNew = false;
	private $lastSetOrderStatusSettingKey = null;

	public function onAfterLoad(Customweb_Database_Entity_IManager $entityManager){
		if ($this->getTransactionObject() !== null && $this->getTransactionObject() instanceof Customweb_Payment_Authorization_ITransaction) {
			$context = $this->getTransactionObject()->getTransactionContext()->getPaymentCustomerContext();
			if ($context instanceof Customweb_Payment_Entity_AbstractPaymentCustomerContext) {
				$contexts = $entityManager->searchByFilterName(get_class($context), 'loadByCustomerId', 
						array(
							'>customerId' => $this->getCustomerId() 
						));
				if (count($contexts) > 0) {
					$currentContext = current($contexts);
					$context->setCustomerId($currentContext->getCustomerId());
					$context->setContextId($currentContext->getContextId());
					$context->setStoreMap($currentContext->getStoreMap());
					$context->setContext($currentContext->getContext());
				}
			}
		}
	}

	public function onBeforeSave(Customweb_Database_Entity_IManager $entityManager){
		if ($this->getTransactionId() === null) {
			$this->isNew = true;
			$this->setCreatedOn(new DateTime());
		}
		
		$this->setUpdatedOn(new DateTime());
		
		if ($this->getTransactionObject() !== null && $this->getTransactionObject() instanceof Customweb_Payment_Authorization_ITransaction) {
			$this->checkIfAuthorizationIsRequired($entityManager);
			$this->checkIfOrderStatusChanged($entityManager);
			
			if ($this->getTransactionObject()->getAliasForDisplay() != null && $this->getTransactionObject()->getAliasForDisplay() != '') {
				$this->setAliasForDisplay($this->getTransactionObject()->getAliasForDisplay());
			}
			$this->setAuthorizationType($this->getTransactionObject()->getAuthorizationMethod());
			$this->setPaymentMachineName($this->getTransactionObject()->getPaymentMethod()->getPaymentMethodName());
			$this->setPaymentId($this->getTransactionObject()->getPaymentId());
			$this->setAuthorizationAmount($this->getTransactionObject()->getAuthorizationAmount());
			$this->setCurrency($this->getTransactionObject()->getCurrencyCode());
			$this->setUpdatable($this->getTransactionObject()->isUpdatable());
			$this->setExecuteUpdateOn($this->getTransactionObject()->getUpdateExecutionDate());
			$this->setAuthorizationStatus($this->getTransactionObject()->getAuthorizationStatus());
			$this->setTransactionExternalId($this->getTransactionObject()->getExternalTransactionId());
			
			$customerId = $this->getTransactionObject()->getTransactionContext()->getOrderContext()->getCustomerId();
			if ($customerId !== null) {
				$this->setCustomerId($customerId);
			}
			$this->setPaid($this->getTransactionObject()->isPaid());
		}
	}

	public function onAfterSave(Customweb_Database_Entity_IManager $entityManager){
		if ($this->getTransactionObject() !== null && $this->getTransactionObject() instanceof Customweb_Payment_Authorization_ITransaction) {
			$paymentCustomerContext = $this->getTransactionObject()->getTransactionContext()->getPaymentCustomerContext();
			
			if ($paymentCustomerContext instanceof Customweb_Payment_Entity_AbstractPaymentCustomerContext) {
				try {
					$paymentCustomerContext->setCustomerId($this->getCustomerId());
					$entityManager->persist($paymentCustomerContext);
				}
				catch(Exception $e) {
					// Ignore
				}
			}
		}
	}

	/**
	 * This method checks if the order status must be updated.
	 *
	 * @param Customweb_Database_Entity_IManager $entityManager
	 */
	protected function checkIfOrderStatusChanged(Customweb_Database_Entity_IManager $entityManager){
		if ($this->getTransactionObject() !== null && $this->getTransactionObject() instanceof Customweb_Payment_Authorization_ITransaction) {
			$lastStatus = $this->getLastSetOrderStatusSettingKey();
			$currentStatus = $this->getTransactionObject()->getOrderStatusSettingKey();
			$method = $this->getTransactionObject()->getPaymentMethod();
			if ($currentStatus !== null && ($lastStatus === null || $lastStatus != $currentStatus) &&
					 $method->existsPaymentMethodConfigurationValue($currentStatus)) {
				$orderStatusId = $method->getPaymentMethodConfigurationValue($currentStatus);
				$this->updateOrderStatus($entityManager, $orderStatusId, $currentStatus);
			}
			$this->setLastSetOrderStatusSettingKey($currentStatus);
		}
	}

	/**
	 * Checks if the transaction must be authorized.
	 * In this case the method calls the method 'authorize()'.
	 *
	 * @param Customweb_Database_Entity_IManager $entityManager
	 */
	protected function checkIfAuthorizationIsRequired(Customweb_Database_Entity_IManager $entityManager){
		$transaction = $this->getTransactionObject();
		if ($transaction !== null && $transaction instanceof Customweb_Payment_Authorization_ITransaction && $transaction->isAuthorized() &&
				 $this->getAuthorizationStatus() === Customweb_Payment_Authorization_ITransaction::AUTHORIZATION_STATUS_PENDING) {
			try {
				$this->authorize($entityManager);
			}
			catch (Exception $e) {
				if ($transaction instanceof Customweb_Payment_Authorization_DefaultTransaction) {
					$transaction->addErrorMessage(
							new Customweb_Payment_Authorization_ErrorMessage(
									Customweb_I18n_Translation::__("The order is not fully completed. Please contact the store owner."), 
									$e->getMessage()));
				}
			}
		}
	}

	/**
	 * This method is called whenever the order status has changed and the system has
	 * to change the order status.
	 *
	 * @param Customweb_Database_Entity_IManager $entityManager
	 */
	abstract protected function updateOrderStatus(Customweb_Database_Entity_IManager $entityManager, $orderStatus, $orderStatusSettingKey);

	/**
	 * This method is invoked, when the authorization should be executed on the shop side.
	 *
	 * @param Customweb_Database_Entity_IManager $entityManager
	 */
	abstract protected function authorize(Customweb_Database_Entity_IManager $entityManager);

	/**
	 * @PrimaryKey
	 */
	public function getTransactionId(){
		return $this->transactionId;
	}

	public function setTransactionId($transactionId){
		$this->transactionId = $transactionId;
		return $this;
	}

	/**
	 * @Column(type = 'varchar')
	 */
	public function getTransactionExternalId(){
		return $this->transactionExternalId;
	}
	
	/**
	 * Alias method for getTransactionExternalId()
	 * 
	 * @return string
	 */
	public function getExternalTransactionId() {
		return $this->getTransactionExternalId();
	}

	public function setTransactionExternalId($transactionExternalId){
		$this->transactionExternalId = $transactionExternalId;
		return $this;
	}

	/**
	 * @Column(type = 'varchar')
	 */
	public function getOrderId(){
		return $this->orderId;
	}

	public function setOrderId($orderId){
		$this->orderId = $orderId;
		return $this;
	}

	/**
	 * @Column(type = 'varchar')
	 */
	public function getAliasForDisplay(){
		return $this->aliasForDisplay;
	}

	public function setAliasForDisplay($aliasForDisplay){
		$this->aliasForDisplay = $aliasForDisplay;
		return $this;
	}

	/**
	 * @Column(type = 'boolean')
	 */
	public function getAliasActive(){
		return $this->aliasActive;
	}

	public function setAliasActive($aliasActive){
		$this->aliasActive = $aliasActive;
		return $this;
	}

	/**
	 * @Column(type = 'varchar')
	 */
	public function getPaymentMachineName(){
		return $this->paymentMachineName;
	}

	public function setPaymentMachineName($paymentMachineName){
		$this->paymentMachineName = $paymentMachineName;
		return $this;
	}

	/**
	 * @Column(type = 'object')
	 *
	 * @return Customweb_Payment_Authorization_ITransaction
	 */
	public function getTransactionObject(){
		return $this->transactionObject;
	}

	public function setTransactionObject($transactionObject){
		$this->transactionObject = $transactionObject;
		return $this;
	}

	/**
	 * @Column(type = 'varchar')
	 */
	public function getAuthorizationType(){
		return $this->authorizationType;
	}

	public function setAuthorizationType($authorizationType){
		$this->authorizationType = $authorizationType;
		return $this;
	}

	/**
	 * @Column(type = 'varchar')
	 */
	public function getCustomerId(){
		return $this->customerId;
	}

	public function setCustomerId($customerId){
		$this->customerId = $customerId;
		return $this;
	}

	/**
	 * @Column(type = 'datetime')
	 *
	 * @return DateTime
	 */
	public function getUpdatedOn(){
		return $this->updatedOn;
	}

	public function setUpdatedOn($updatedOn){
		$this->updatedOn = $updatedOn;
		return $this;
	}

	/**
	 * @Column(type = 'datetime')
	 *
	 * @return DateTime
	 */
	public function getCreatedOn(){
		return $this->createdOn;
	}

	public function setCreatedOn($createdOn){
		$this->createdOn = $createdOn;
		return $this;
	}

	/**
	 * @Column(type = 'varchar')
	 */
	public function getPaymentId(){
		return $this->paymentId;
	}

	public function setPaymentId($paymentId){
		$this->paymentId = $paymentId;
		return $this;
	}

	/**
	 * @Column(type = 'boolean')
	 */
	public function getUpdatable(){
		return $this->updatable;
	}

	public function setUpdatable($updatable){
		$this->updatable = $updatable;
		return $this;
	}

	/**
	 * @Column(type = 'datetime')
	 */
	public function getExecuteUpdateOn(){
		return $this->executeUpdateOn;
	}

	public function setExecuteUpdateOn($executeUpdateOn){
		$this->executeUpdateOn = $executeUpdateOn;
		return $this;
	}

	/**
	 * @Column(type = 'decimal')
	 */
	public function getAuthorizationAmount(){
		return $this->authorizationAmount;
	}

	public function setAuthorizationAmount($authorizationAmount){
		$this->authorizationAmount = $authorizationAmount;
		return $this;
	}

	/**
	 * @Column(type = 'varchar')
	 */
	public function getAuthorizationStatus(){
		return $this->authorizationStatus;
	}

	public function setAuthorizationStatus($authorizationStatus){
		$this->authorizationStatus = $authorizationStatus;
		return $this;
	}

	/**
	 * @Column(type = 'boolean')
	 */
	public function getPaid(){
		return $this->paid;
	}

	public function setPaid($paid){
		$this->paid = $paid;
		return $this;
	}

	/**
	 * @Column(type = 'varchar')
	 */
	public function getCurrency(){
		return $this->currency;
	}

	public function setCurrency($currency){
		$this->currency = $currency;
		return $this;
	}

	/**
	 * @Column(type = 'varchar')
	 */
	public function getLastSetOrderStatusSettingKey(){
		return $this->lastSetOrderStatusSettingKey;
	}

	public function setLastSetOrderStatusSettingKey($lastSetOrderStatusSettingKey){
		$this->lastSetOrderStatusSettingKey = $lastSetOrderStatusSettingKey;
		return $this;
	}
}