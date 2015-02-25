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

class Customweb_PayboxCw_Model_PaymentCustomerContext implements Customweb_Payment_Authorization_IPaymentCustomerContext
{
	private $context = null;
	private $customerId = null;

	public function __construct($customerId)
	{
		$this->customerId = $customerId;
	}

	public function getMap()
	{
		return $this->getContext()
			->getMap();
	}

	public function updateMap(array $update)
	{
		return $this->getContext()
			->updateMap($update);
	}

	public function persist()
	{
		if ($this->customerId > 0) {

			$loadedContextMap = $this->getContextMapFromDatabase();
			$updatedMap = array();
			if ($loadedContextMap !== null) {
				$updatedMap = $this->getContext()
					->applyUpdatesOnMapAndReset($loadedContextMap);
			} else {
				$updatedMap = $this->getContext()
					->getMap();
			}

			$this->updateContextMapInDatabase($updatedMap);
		}
	}

	public function __sleep()
	{
		$this->persist();
		return array(
			'customerId'
		);
	}

	public function __wakeup()
	{
	}

	/**
	 * @return Customweb_Payment_Authorization_DefaultPaymentCustomerContext
	 */
	protected function getContext()
	{
		if ($this->context === null) {
			if ($this->customerId > 0) {
				$map = $this->getContextMapFromDatabase();
				if ($map === null) {
					$map = array();
				}
				$this->context = new Customweb_Payment_Authorization_DefaultPaymentCustomerContext($map);
			} else {
				$this->context = new Customweb_Payment_Authorization_DefaultPaymentCustomerContext(array());
			}
		}
		return $this->context;
	}

	/**
	 * @return array
	 */
	protected function getContextMapFromDatabase()
	{
		$customerContext = Mage::getModel('payboxcw/customercontext')->load($this->customerId);
		if ($customerContext->getId() && $customerContext->getContext()) {
			$result = Mage::helper('PayboxCw')->unserialize($customerContext->getContext());

			if ($result instanceof Customweb_Payment_Authorization_IPaymentCustomerContext) {
				return $result->getMap();
			} else {
				return $result;
			}
		} else {
			return null;
		}
	}

	protected function updateContextMapInDatabase(array $map)
	{
		$customerContext = Mage::getModel('payboxcw/customercontext')->load($this->customerId);
		if (!$customerContext->getId()) {
			$customerContext = Mage::getModel('payboxcw/customercontext');
			$customerContext->setId($this->customerId);
		}

		$customerContext->setContext(Mage::helper('PayboxCw')->serialize($map));
		$customerContext->save();
	}
}
