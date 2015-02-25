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

class Customweb_PayboxCw_Model_OrderContext extends Customweb_Payment_Authorization_OrderContext_AbstractDeprecated implements Customweb_Payment_Authorization_IOrderContext
{
	/**
	 * Transient fields
	 */
	private $orderCache = null;
	private $quoteCache = null;

	/**
	 * Fields beeing serialized
	 *           	   	  	 	  
	 */
	private $orderId = null;
	private $quoteId = null;
	private $isOrderAvailable = null;
	private $paymentMethod = null;
	private $storeId = null;
	private $languageCode = "en_US";
	private $currencyCode = null;
	private $useBaseCurrency = false;
	private $orderParameters = array();

	/**
	 * 
	 * @param Customweb_PayboxCw_Model_Method $paymentMethod
	 * @param unknown $isOrderAvailable
	 */
	public function __construct(Customweb_PayboxCw_Model_Method $paymentMethod, $isOrderAvailable)
	{
		$this->isOrderAvailable = $isOrderAvailable;
		$this->paymentMethod = $paymentMethod;
		$this->storeId = Mage::app()->getStore()->getStoreId();
		$this->languageCode = Mage::app()->getLocale()->getLocaleCode();
		
		if ($paymentMethod->getPaymentMethodConfigurationValue('use_base_currency')) {
			$this->useBaseCurrency = true;
			$this->currencyCode = $this->getStore()->getBaseCurrencyCode();
		} else {
			$this->useBaseCurrency = false;
			$this->currencyCode = $this->getStore()->getCurrentCurrencyCode();
		}
	}

	public function __sleep()
	{
		return array(
			'isOrderAvailable',
			'paymentMethod',
			'orderId',
			'quoteId',
			'storeId',
			'languageCode',
			'currencyCode',
			'useBaseCurrency'
		);
	}
	
	/**
	 * @return boolean
	 */
	public function useBaseCurrency() {
		return $this->useBaseCurrency;
	}
	
	/**
	 * @return Mage_Core_Model_Store
	 */
	public function getStore() {
		return Mage::app()->getStore($this->storeId);
	}

	public static function fromOrder($order)
	{
		$orderContext = new Customweb_PayboxCw_Model_OrderContext($order->getPayment()
			->getMethodInstance(), true);
		$orderContext->setOrder($order);
		return $orderContext;
	}

	public function getCustomerId()
	{
		return $this->getOrderQuote()
			->getCustomerId();
	}

	public function isNewCustomer()
	{
		$customerId = $this->getCustomerId();
		$orders = Mage::getResourceModel('sales/order_collection')->addFieldToSelect('*')
			->addFieldToFilter('customer_id', $customerId);

		foreach ($orders as $order) {
			if ($order->getState() == 'complete') {
				return 'existing';
			}
		}

		return 'new';
	}

	public function getCustomerRegistrationDate()
	{
		$customerId = $this->getCustomerId();
		$customer = Mage::getModel('customer/customer')->load($customerId);
		return new DateTime($customer->getCreatedAt());
	}

	public function getOrderAmountInDecimals()
	{
		if ($this->useBaseCurrency()) {
			return $this->getOrderQuote()->getBaseGrandTotal();
		} else {
			return $this->getOrderQuote()->getGrandTotal();
		}
	}

	public function getCurrencyCode()
	{
		if ($this->currencyCode === null) {
			return $this->getStore()->getCurrentCurrencyCode();
		}
		else {
			return $this->currencyCode;
		}
	}

	public function getInvoiceItems()
	{
		$helper = Mage::helper('PayboxCw');

		$resultItems = array();
		$orderItems = $this->getOrderQuote()
			->getItemsCollection();

		foreach ($orderItems as $item) {
			if ($item->getParentItemId() != null && $item->getParentItem()
				->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
				continue;
			}
			if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $item->getParentItemId() == null) {
				continue;
			}

			$productItem = $helper->getProductItem($item, $this->useBaseCurrency());
			if ($productItem) {
				$resultItems[] = $productItem;
			}

			$discountItem = $helper->getDiscountItem($item, $this->useBaseCurrency());
			if ($discountItem) {
				$resultItems[] = $discountItem;
			}
		}

		$surchargeItem = $helper->getFoomanSurchargeItem($this->getOrderQuote(), $this->useBaseCurrency());
		if ($surchargeItem) {
			$resultItems[] = $surchargeItem;
		}

		$shippingItem = $this->getShippingItem();
		if ($shippingItem) {
			$resultItems[] = $shippingItem;
		}
		
		$resultItems = Customweb_Util_Invoice::ensureUniqueSku($resultItems);
		
		$event = new StdClass;
		$event->items = array();
		
		if ($this->getOrderQuote() instanceof Mage_Sales_Model_Order) {
			Mage::dispatchEvent('customweb_collect_order_items', array(
				'order' => $this->getOrderQuote(),
				'useBaseCurrency' => $this->useBaseCurrency(),
				'result' => $event
			));
		} elseif ($this->getOrderQuote() instanceof Mage_Sales_Model_Quote) {
			Mage::dispatchEvent('customweb_collect_quote_items', array(
				'quote' => $this->getOrderQuote(),
				'useBaseCurrency' => $this->useBaseCurrency(),
				'result' => $event
			));
		}
		
		foreach ($event->items as $item) {
			$resultItems[] = new Customweb_Payment_Authorization_DefaultInvoiceItem($item['sku'], $item['name'], $item['taxRate'], $item['amountIncludingTax'], $item['quantity'], $item['type']);
		}
		
		$adjustmentItem = $helper->getAdjustmentItem($resultItems, $this->getOrderAmountInDecimals(), $this->getCurrencyCode());
		if ($adjustmentItem) {
			$resultItems[] = $adjustmentItem;
		}
		
		return $resultItems;
	}

	public function getShippingMethod()
	{
		$shippingAddress = $this->getOrderQuote()->getShippingAddress();
		if ($shippingAddress != null) {
			return $shippingAddress->getShippingMethod();
		}
	}

	public function getPaymentMethod()
	{
		return $this->paymentMethod;
	}

	public function getLanguage()
	{
		return new Customweb_Core_Language($this->languageCode);
	}

	public function getCustomerEMailAddress()
	{
		$customerId = $this->getCustomerId();
		$customer = Mage::getModel('customer/customer')->load($customerId);
		$customerMail = $customer->getEmail();
		if (empty($customerMail)) {
			return $this->getBillingEMailAddress();
		} else {
			return $customerMail;
		}
	}

	public function getBillingEMailAddress()
	{
		return $this->getInternalBillingAddress()
			->getEmail();
	}

	public function getBillingGender()
	{
		$gender = $this->getOrderQuote()->getCustomerGender();
		
		$customerId = $this->getCustomerId();
		$customer = Mage::getModel('customer/customer')->load($customerId);
		
		if ($gender !== null) {
			$gender = $customer->getAttribute('gender')->getSource()->getOptionText($gender);
			return strtolower($gender);
		}
		
		if ($customer->getGender() !== null) {
			$gender = $customer->getAttribute('gender')->getSource()->getOptionText($customer->getGender());
			return strtolower($gender);
		}
	}

	public function getBillingSalutation()
	{
		return null;
	}

	public function getBillingFirstName()
	{
		return $this->getInternalBillingAddress()
			->getFirstname();
	}

	public function getBillingLastName()
	{
		return $this->getInternalBillingAddress()
			->getLastname();
	}

	public function getBillingStreet()
	{
		return implode(',', $this->getInternalBillingAddress()
			->getStreet());
	}

	public function getBillingCity()
	{
		return $this->getInternalBillingAddress()
			->getCity();
	}

	public function getBillingPostCode()
	{
		return $this->getInternalBillingAddress()
			->getPostcode();
	}

	public function getBillingState()
	{
		return $this->getInternalBillingAddress()
			->getRegionCode();
	}

	public function getBillingCountryIsoCode()
	{
		return $this->getInternalBillingAddress()
			->getCountryId();
	}

	public function getBillingPhoneNumber()
	{
		return $this->getInternalBillingAddress()
			->getTelephone();
	}

	public function getBillingMobilePhoneNumber()
	{
		return null;
	}

	public function getBillingDateOfBirth()
	{
		$dob = $this->getOrderQuote()->getCustomerDob();
		
		if ($dob !== null) {
			return new DateTime($dob);
		}
		
		$customerId = $this->getCustomerId();
		$customer = Mage::getModel('customer/customer')->load($customerId);
		$dob = $customer->getDob();

		if ($dob !== null) {
			return new DateTime($dob);
		} else {
			return null;
		}
	}

	public function getBillingCommercialRegisterNumber()
	{
		return null;
	}

	public function getBillingCompanyName()
	{
		$this->getInternalBillingAddress()
			->getCompany();
	}

	public function getBillingSalesTaxNumber()
	{
		return null;
	}

	public function getBillingSocialSecurityNumber()
	{
		return null;
	}

	public function getShippingEMailAddress()
	{
		return $this->getInternalShippingAddress()
			->getEmail();
	}

	public function getShippingGender()
	{
		return null;
	}

	public function getShippingSalutation()
	{
		return null;
	}

	public function getShippingFirstName()
	{
		return $this->getInternalShippingAddress()
			->getFirstname();
	}

	public function getShippingLastName()
	{
		return $this->getInternalShippingAddress()
			->getLastname();
	}

	public function getShippingStreet()
	{
		return implode(',', $this->getInternalShippingAddress()
			->getStreet());
	}

	public function getShippingCity()
	{
		return $this->getInternalShippingAddress()
			->getCity();
	}

	public function getShippingPostCode()
	{
		return $this->getInternalShippingAddress()
			->getPostcode();
	}

	public function getShippingState()
	{
		return $this->getInternalShippingAddress()
			->getRegionCode();
	}

	public function getShippingCountryIsoCode()
	{
		return $this->getInternalShippingAddress()
			->getCountryId();
	}

	public function getShippingPhoneNumber()
	{
		return $this->getInternalShippingAddress()
			->getTelephone();
	}

	public function getShippingMobilePhoneNumber()
	{
		return null;
	}

	public function getShippingDateOfBirth()
	{
		return null;
	}

	public function getShippingCompanyName()
	{
		$this->getInternalShippingAddress()
			->getCompany();
	}

	public function getShippingCommercialRegisterNumber()
	{
		return null;
	}

	public function getShippingSalesTaxNumber()
	{
		return null;
	}

	public function getShippingSocialSecurityNumber()
	{
		return null;
	}

	public function getOrderParameters()
	{
		return $this->orderParameters;
	}
	
	public function setOrderParameters(array $parameters) {
		$this->orderParameters = $parameters;
		return $this;
	}
	
	public function addOrderParameter($name, $value) {
		$this->orderParameters[$name] = $value;
		return $this;
	}
	
	public function getCheckoutId()
	{
		return $this->getQuote()->getId();
	}

	private function getOrderQuote()
	{
		if ($this->isOrderAvailable) {
			return $this->getOrder();
		} else {
			return $this->getQuote();
		}
	}

	/**
	 * Returns the order from the checkout session
	 *
	 *  @return	  Mage_Sales_Model_Order
	 */
	private function getOrder()
	{
		if ($this->orderCache == null) {
			$order = Mage::getModel('sales/order');
			if ($this->orderId == null) {
				$session = Mage::getSingleton('checkout/session');
				$order->loadByIncrementId($session->getLastRealOrderId());
			} else {
				$order->load($this->orderId);

			}
			$this->setOrder($order);
		}
		return $this->orderCache;
	}

	private function setOrder($order)
	{
		$this->orderCache = $order;
		$this->orderId = $order->getId();
		$quote = Mage::getModel('sales/quote');
		$quote = $quote->setStoreId($order->getStoreId())
			->load((int) $order->getQuoteId());
		$this->setQuote($quote);
	}

	private function getQuote()
	{
		if ($this->quoteCache == null) {
			if ($this->quoteId == null) {
				$quote = Mage::getSingleton('checkout/session')->getQuote();
				if (!$quote->getId()) {
					$quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
				}
				$this->quoteId = $quote->getId();
				$this->quoteCache = $quote;
			} else {
				$quote = Mage::getModel('sales/quote');
				$quote->load($this->quoteId);
				$this->quoteCache = $quote;
			}

		}
		return $this->quoteCache;
	}

	private function setQuote($quote)
	{
		$this->quoteCache = $quote;
		$this->quoteId = $quote->getId();
	}

	private function getInternalBillingAddress()
	{
		return $this->getOrderQuote()
			->getBillingAddress();
	}

	private function getInternalShippingAddress()
	{
		$shippingAddress = $this->getOrderQuote()
			->getShippingAddress();
		if ($shippingAddress == null) {
			$shippingAddress = $this->getInternalBillingAddress();
		}
		return $shippingAddress;
	}

	/**
	 *
	 * @return Customweb_Payment_Authorization_IInvoiceItem
	 */
	private function getShippingItem()
	{
		if ($this->getOrderQuote() instanceof Mage_Sales_Model_Quote) {
			$shippingInfo = $this->getOrderQuote()->getShippingAddress();
		} else {
			$shippingInfo = $this->getOrderQuote();
		}

		// Check if we need to add shipping           	   	  	 	  
		if ($shippingInfo->getShippingAmount() > 0) {
			$sku = 'shipping';
			$shippingTaxRate = Mage::helper('PayboxCw')->getShippingTaxRate($this->getOrderQuote());
			if ($this->useBaseCurrency()) {
				$shippingCostExclTax = $shippingInfo->getBaseShippingAmount();
				$shippingTax = $shippingInfo->getBaseShippingTaxAmount();
			} else {
				$shippingCostExclTax = $shippingInfo->getShippingAmount();
				$shippingTax = $shippingInfo->getShippingTaxAmount();
			}
			$shippingCostIncTax = $shippingCostExclTax + $shippingTax;
			$shippingName = $shippingInfo->getShippingDescription();
			$quantity = 1;
			$type = Customweb_Payment_Authorization_IInvoiceItem::TYPE_SHIPPING;

			$shippingItem = new Customweb_Payment_Authorization_DefaultInvoiceItem($sku, $shippingName, $shippingTaxRate, $shippingCostIncTax, $quantity, $type);
			return $shippingItem;
		}
		return null;
	}
}
