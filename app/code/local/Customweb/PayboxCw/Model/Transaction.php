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
 *
 * @method int getTransactionId()
 * @method Customweb_PayboxCw_Model_Transaction setTransactionId(int $value)
 * @method string getTransactionExternalId()
 * @method Customweb_PayboxCw_Model_Transaction setTransactionExternalId(string $value)
 * @method int getOrderId()
 * @method Customweb_PayboxCw_Model_Transaction setOrderId(int $value)
 * @method int getOrderPaymentId()
 * @method Customweb_PayboxCw_Model_Transaction setOrderPaymentId(int $value)
 * @method string getAliasForDisplay()
 * @method Customweb_PayboxCw_Model_Transaction setAliasForDisplay(string $value)
 * @method boolean getAliasActive()
 * @method Customweb_PayboxCw_Model_Transaction setAliasActive(boolean $value)
 * @method string getPaymentMethod()
 * @method Customweb_PayboxCw_Model_Transaction setPaymentMethod(string $value)
 * @method Customweb_Payment_Authorization_ITransaction getTransactionObject()
 * @method Customweb_PayboxCw_Model_Transaction setTransactionObject(Customweb_Payment_Authorization_ITransaction $value)
 * @method string getAuthorizationType()
 * @method Customweb_PayboxCw_Model_Transaction setAuthorizationType(string $value)
 * @method int getCustomerId()
 * @method Customweb_PayboxCw_Model_Transaction setCustomerId(int $value)
 * @method string getUpdatedOn()
 * @method Customweb_PayboxCw_Model_Transaction setUpdatedOn(string $value)
 * @method string getCreatedOn()
 * @method Customweb_PayboxCw_Model_Transaction setCreatedOn(string $value)
 * @method string getPaymentId()
 * @method Customweb_PayboxCw_Model_Transaction setPaymentId(string $value)
 * @method boolean getUpdatable()
 * @method Customweb_PayboxCw_Model_Transaction setUpdatable(boolean $value)
 * @method string getExecuteUpdateOn()
 * @method Customweb_PayboxCw_Model_Transaction setExecuteUpdateOn(string $value)
 * @method float getAuthorizationAmount()
 * @method Customweb_PayboxCw_Model_Transaction setAuthorizationAmount(float $value)
 * @method string getAuthorizationStatus()
 * @method Customweb_PayboxCw_Model_Transaction setAuthorizationStatus(string $value)
 * @method boolean getPaid()
 * @method Customweb_PayboxCw_Model_Transaction setPaid(boolean $value)
 * @method string getCurrency()
 * @method Customweb_PayboxCw_Model_Transaction setCurrency(string $value)
 */
class Customweb_PayboxCw_Model_Transaction extends Mage_Core_Model_Abstract
{
	private $_order = null;
	
	private $ignoreStatus = false;
	
	private $authorizationStatusBefore = null;

	protected function _construct()
	{
		$this->_init('payboxcw/transaction');
	}

	protected function _afterLoad()
	{
		parent::_afterLoad();

		if (is_string($this->getTransactionObject())) {
			$this->setTransactionObject(Mage::helper('PayboxCw')->unserialize($this->getTransactionObject()));
			$orderId = $this->getOrderId();
			if (!empty($orderId)) {
				Customweb_PayboxCw_Model_ConfigurationAdapter::setStore($this->getOrder());
			}
		}
	}

	public function save()
	{
		$this->_hasDataChanges = true;
		return parent::save();
	}
	
	public function saveIgnoreOrderStatus()
	{
		$this->ignoreStatus = true;
		$result = $this->save();
		$this->ignoreStatus = false;
		return $result;
	}

	protected function _beforeSave()
	{
		parent::_beforeSave();

		if ($this->isObjectNew()) {
			$this->setCreatedOn(date("Y-m-d H:i:s"));
		}

		$this->setUpdatedOn(date("Y-m-d H:i:s"));

		if ($this->getTransactionObject() !== null && $this->getTransactionObject() instanceof Customweb_Payment_Authorization_ITransaction) {
			$this->authorizationStatusBefore = $this->getAuthorizationStatus();
			$this->checkIfOrderStatusChanged();
			
			$aliasManagerActive = ($this->getTransactionObject()->getPaymentMethod()->getPaymentMethodConfigurationValue('alias_manager') == 'active');
			$this->setAliasActive($aliasManagerActive);

			if ($aliasManagerActive && $this->getTransactionObject()->getAliasForDisplay() != null && $this->getTransactionObject()->getAliasForDisplay() != '') {
				$this->setAliasForDisplay($this->getTransactionObject()->getAliasForDisplay());
			}
			$this->setAuthorizationType($this->getTransactionObject()->getAuthorizationMethod());
			$this->setPaymentMethod($this->getTransactionObject()->getPaymentMethod()->getCode());
			$this->setPaymentId($this->getTransactionObject()->getPaymentId());
			$this->setAuthorizationAmount($this->getTransactionObject()->getAuthorizationAmount());
			$this->setCurrency($this->getTransactionObject()->getCurrencyCode());
			$executeUpdateOn = $this->getTransactionObject()->getUpdateExecutionDate();
			if ($executeUpdateOn instanceof DateTime) {
				$executeUpdateOn = $executeUpdateOn->format('Y-m-d H:i:s');
			}
			$this->setExecuteUpdateOn($executeUpdateOn);
			$this->setAuthorizationStatus($this->getTransactionObject()->getAuthorizationStatus());
			$this->setCustomerId($this->getTransactionObject()->getTransactionContext()->getOrderContext()->getCustomerId());
			$this->setPaid($this->getTransactionObject()->isPaid());
			$this->setTransactionExternalId($this->getTransactionObject()->getExternalTransactionId());
		}

		if (!is_string($this->getTransactionObject())) {
			$this->setTransactionObject(Mage::helper('PayboxCw')->serialize($this->getTransactionObject()));
		}
	}

	protected function _afterSave()
	{
		parent::_afterSave();

		if (is_string($this->getTransactionObject())) {
			$this->setTransactionObject(Mage::helper('PayboxCw')->unserialize($this->getTransactionObject()));
		}

		if ($this->getAliasActive() && $this->getAliasForDisplay() !== null) {
			$collection = Mage::getModel('payboxcw/transaction')->getCollection()
				->addFieldToFilter('customer_id', array(
					'eq' => $this->getCustomerId()
				))
				->addFieldToFilter('payment_method', array(
					'eq' => $this->getPaymentMethod()
				))
				->addFieldToFilter('alias_active', array(
					'eq' => 1
				))
				->addFieldToFilter('alias_for_display', array(
					'eq' => $this->getAliasForDisplay()
				))
				->addFieldToFilter('transaction_id', array(
					'neq' => $this->getTransactionId()
				));
			foreach ($collection as $transaction) {
				if ($transaction->getTransactionId() < $this->getTransactionId()) {
					$transaction->setAliasActive(false)->save();
				}
			}
		}
		
		$this->checkIfAuthorizationIsRequired();
		$this->authorizationStatusBefore = null;
	}
	
	/**
	 * Checks if the transaction must be authorized. In this case the method calls the method 'authorize()'.
	 *
	 * @param Customweb_Database_Entity_IManager $entityManager
	 */
	protected function checkIfAuthorizationIsRequired() {
		if ($this->getTransactionObject() !== null && $this->getTransactionObject()->isAuthorized() && $this->authorizationStatusBefore === Customweb_Payment_Authorization_ITransaction::AUTHORIZATION_STATUS_PENDING) {
			$this->authorize();
		}
	}
	
	/**
	 * This method is invoked, when the authorization should be executed on the shop side.
	 *
	 * @param Customweb_Database_Entity_IManager $entityManager
	 */
	protected function authorize() {
		$order = $this->getOrder(false);
		$order->getPayment()->getMethodInstance()->processPayment($order, $this);
	}
	
	/**
	 * This method checks if the order status must be updated.
	 *
	 * @param Customweb_Database_Entity_IManager $entityManager
	 */
	protected function checkIfOrderStatusChanged() {
		if ($this->ignoreStatus) {
			return;
		}
		if ($this->getTransactionObject() !== null && $this->getTransactionObject() instanceof Customweb_Payment_Authorization_ITransaction) {
			$lastStatus = $this->getLastSetOrderStatusSettingKey();
			$currentStatus = $this->getTransactionObject()->getOrderStatusSettingKey();
			$method = $this->getTransactionObject()->getPaymentMethod();
			if ($currentStatus !== null && ($lastStatus === null || $lastStatus != $currentStatus) && $method->existsPaymentMethodConfigurationValue($currentStatus)) {
				$orderStatusId = $method->getPaymentMethodConfigurationValue($currentStatus);
				$this->updateOrderStatus($orderStatusId, $currentStatus);
			}
			$this->setLastSetOrderStatusSettingKey($currentStatus);
		}
	}
	
	/**
	 * This method is called whenever the order status has changed and the system has
	 * to change the order status.
	 */
	protected function updateOrderStatus($orderStatus, $orderStatusSettingKey) {
		try {
			$order = $this->getOrder(false);
			
			if ($order->getStatus() == $orderStatus) {
				return;
			}
			
			$order->setStatus($orderStatus);
			$order->save();
			$order->addStatusToHistory($orderStatus);
			$order->save();
				
			Mage::dispatchEvent('payboxcw_order_status', array(
				'order' => $order,
				'status' => $orderStatus
			));
		} catch (Exception $e) {}
	}

	public function getOrder($cached = true)
	{
		if ($this->_order == null || !$cached) {
			$this->_order = Mage::getModel('sales/order')->load($this->getOrderId());
		}
		return $this->_order;
	}
	
	public function getExternalTransactionId() {
		return $this->getTransactionExternalId();
	}
}