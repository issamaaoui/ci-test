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

// Load our custom translation resolver
new Customweb_PayboxCw_Model_TranslationResolver();

abstract class Customweb_PayboxCw_Model_Method extends Mage_Payment_Model_Method_Abstract implements Customweb_Payment_Authorization_IPaymentMethod
{
	protected $_code = 'payboxcw';
	protected $_formBlockType = 'payboxcw/form';
	protected $_infoBlockType = 'payboxcw/info';

	/**
	 * Order statuses           	   	  	 	  
	 */
	const PAYBOXCW_STATUS_PENDING = 'pending_payboxcw';
	const PAYBOXCW_STATUS_CANCELED = 'canceled_payboxcw';

	const STATE_CANCELLED = 'canceled';
	const STATE_PENDING = 'pending_payment';
	const STATE_PROCESSING = 'processing';

	protected $authorizationAdapter = null;

	protected $orderContext = null;

	protected $helper = null;

	protected static $formFields = array();

	/**
	 * Standard payment method flags
	 */
	protected $_isGateway = true;
	protected $_canAuthorize = true;
	protected $_canCapture = true;
	protected $_canCapturePartial = true;
	protected $_canRefund = true;
	protected $_canRefundInvoicePartial = true;
	protected $_canVoid = true;
	protected $_canUseInternal = true;
	protected $_canUseCheckout = true;
	protected $_canUseForMultishipping = false;
	protected $_isInitializeNeeded = true;
	protected $paymentMethodName = "genericpaymentmethod";
	protected $authorizationMethodName = null;

	public function __sleep()
	{
		return array(
			'_code',
			'_formBlockType',
			'_isGateway',
			'_canAuthorize',
			'_canCapture',
			'_canCapturePartial',
			'_canRefund',
			'_canRefundInvoicePartial',
			'_canVoid',
			'_canUseInternal',
			'_canUseCheckout',
			'_canUseForMultishipping',
			'paymentMethodName',
			'authorizationMethodName'
		);
	}
	
	protected function setOrderStatus(&$order, $status, $logEntry = null)
	{
		try {
			if ($order->getStatus() == $status) {
				return;
			}
			
			$order->setStatus($status);
			$order->save();
			$order->addStatusToHistory($status, $logEntry);
			$order->save();
			
			Mage::dispatchEvent('payboxcw_order_status', array(
				'order' => $order,
				'status' => $status
			));
		} catch (Exception $e) {}
		return $this;
	}

	/**
	 * Check whether payment method can be used
	 *
	 * @param Mage_Sales_Model_Quote|null $quote
	 * @return bool
	 */
	public function isAvailable($quote = null)
	{
		$isAvailable = parent::isAvailable($quote);

		if ($isAvailable) {
			$allowedCurrencies = $this->getPaymentMethodConfigurationValue('Currency');
			if ($quote !== null && !empty($allowedCurrencies) && (!isset($allowedCurrencies[0]) || $allowedCurrencies[0] != 1)) {
				$isAvailable = (in_array($quote->getQuoteCurrencyCode(), $allowedCurrencies));
			}
		}

		if ($isAvailable) {
			try {
				$paymentContext = $this->getHelper()->getPaymentCustomerContext();
				$orderContext = $this->getOrderContext(false);
				$this->getAuthorizationAdapter(false)->preValidate($orderContext, $paymentContext);
				$isAvailable = true;
			} catch (Exception $e) {
				$isAvailable = false;
			}
		}
		
		return $isAvailable;
	}
	
	/**
	 * This method is called during order creation to initialize the payment.
	 *
	 * @param string $paymentAction
	 * @param Varien_Object $stateObject
	 * @return Customweb_PayboxCw_Model_Method
	 */
	public function initialize($paymentAction, $stateObject)
	{
		$stateObject->setStatus(self::PAYBOXCW_STATUS_PENDING);
		$stateObject->setState(self::STATE_PENDING);
		$stateObject->setIsNotified(false);
		return $this;
	}

	public function assignData($data)
	{
		// Call parent assignData
		parent::assignData($data);

		// Save payment form data
		$info = $this->getInfoInstance();
		$info->unsAdditionalInformation();

		if (isset($_REQUEST[$this->getCode()])) {
			$formData = $_REQUEST[$this->getCode()];
			foreach ($formData as $key => $value) {
				$info->setAdditionalInformation($key, $value);
			}
		}

		return $this;
	}

	/**
	 * Validate after this payment method has been chosen, wheter this is valid.
	 *
	 * @return	boolean
	 */
	public function validate()
	{
		
		$arguments = null;
		return Customweb_Licensing_PayboxCw_License::run('9njpqddlesmvpsgj', $this, $arguments);
	}

	public function call_9kb15o2762um9ko0() {
		$arguments = func_get_args();
		$method = $arguments[0];
		$call = $arguments[1];
		$parameters = array_slice($arguments, 2);
		if ($call == 's') {
			return call_user_func_array(array(get_class($this), $method), $parameters);
		}
		else {
			return call_user_func_array(array($this, $method), $parameters);
		}
		
		
	}

	/**
	 * This overwrites the default behavior that retrieves the payment action from
	 * the magento configuration. We need this value to be set to none of the existing
	 * actions, which normally is the case. As other installed payment modules might overwrite
	 * these defaults, we ignore the configuration value here.
	 *          	   	  	 	  
	 * @return String
	 */
	public function getConfigPaymentAction()
	{
		return 'nothing';
	}

	/**
	 * Returns the module description
	 *
	 * @return	string
	 */
	public function getDescription()
	{
		if ($this->moduleDescription == null) {
			$this->moduleDescription = $this->getPaymentMethodConfigurationValue('description');
		}

		return $this->moduleDescription;
	}

	/**
	 * Update transaction ids for further processing
	 * If no transactions were set before invoking, may generate an "offline" transaction id
	 *
	 * @param string $type
	 * @param Mage_Sales_Model_Order_Payment_Transaction $transactionBasedOn
	 */
	protected function _generateTransactionId($payment, $type, $transactionBasedOn = false)
	{
		if (!$payment->getParentTransactionId() && $transactionBasedOn) {
			$payment->setParentTransactionId($transactionBasedOn->getTxnId());
		}
		// generate transaction id for an offline action or payment method that didn't set it
		if (($parentTxnId = $payment->getParentTransactionId())) {
			$transactionId = "{$parentTxnId}-{$type}";

			$collection = Mage::getModel('sales/order_payment_transaction')->getCollection()
				->setOrderFilter($payment->getOrder())
				->addPaymentIdFilter($payment->getId())
				->addTxnTypeFilter($type);
			if ($collection->count() > 0) {
				$transactionId .= '-' . ($collection->count() + 1);
			}

			$payment->setTransactionId($transactionId);
		}
	}

	/**
	 * Send authorize request to gateway
	 *
	 * @param   Varien_Object $payment
	 * @param   decimal $amount
	 * @return  Customweb_PayboxCw_Model_Method
	 */
	public function authorize(Varien_Object $payment, $amount)
	{
		Mage::log('Authorize was called');
		Mage::throwException('Authorization was called inapropriately.');
		return $this;
	}
	
	/**
	 * Process the authorization for server.
	 * 
	 * @param Customweb_Payment_Authorization_ITransaction $transaction
	 * @param array $parameters
	 * @return string
	 */
	public function processServerAuthorization(Customweb_PayboxCw_Model_Transaction $transaction, array $parameters)
	{
		$adapter = $this->getAuthorizationAdapter(true, $transaction);
		if (!($adapter instanceof Customweb_Payment_Authorization_Server_IAdapter)) {
			Mage::throwException('The adapter is not of type server.');
		}
		return $adapter->processAuthorization($transaction->getTransactionObject(), $parameters);
	}

	/**
	 * Cancel payment abstract method
	 *
	 * @param Varien_Object $payment
	 *
	 * @return Mage_Payment_Model_Abstract
	 */
	public function cancel(Varien_Object $payment)
	{
		
		try {
			$transaction = $this->getHelper()->loadTransactionByPayment($payment->getId());

			$order = $payment->getOrder();
			Customweb_PayboxCw_Model_ConfigurationAdapter::setStore($order);
			$adapter = $this->getHelper()->createContainer()->getBean('Customweb_Payment_BackendOperation_Adapter_Service_ICancel');
			if ($transaction->getTransactionObject()->isCancelPossible()) {
				$adapter->cancel($transaction->getTransactionObject());
				$transaction->save();
			}

			if (!$transaction->getTransactionObject()->isCancelled() && $order->getStatus() != self::PAYBOXCW_STATUS_PENDING) {
				Mage::log("Method::cancel() Cancellation of payment is not possible" . print_r($transaction->getTransactionObject()->getErrorMessages(), true));
				Mage::throwException($this->getHelper()->__('Canceling of this payment not possible!'));
			}

			if ($transaction->getTransactionObject()->isCancelled()) {
				$authTransaction = $payment->getAuthorizationTransaction();
				if ($authTransaction) {
					$parentTxnId = $authTransaction->getTxnId();
					if (method_exists($payment, 'lookupTransaction') && $payment->lookupTransaction($parentTxnId . '-void') === false) {
						$payment->setParentTransactionId($parentTxnId)->setIsTransactionClosed(true);
						$cancels = $transaction->getTransactionObject()->getCancels();
						$cancel = end($cancels);
						$payment->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, array(
							'TRANSACTIONID' => $transaction->getTransactionObject()->getPaymentId(),
							'CANCELID' => $cancel->getCancelId(),
							'STATUS' => $cancel->getStatus()
						));
						$this->_generateTransactionId($payment, Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID);
						$paymentTransaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID);
						$paymentTransaction->save();
					}
				}
			}

			return $this;
		} catch (Exception $e) {
			Mage::log('Method::cancel() ' . $e->getMessage());
			Mage::getSingleton('core/session')->addError($e->getMessage());
			throw $e;
		}
		
		
	}

	/**
	 * Refund money
	 *
	 * @param Varien_Object $payment
	 * @param float $amount
	 *
	 * @return  Mage_PayboxCw_Model_Method
	 */
	public function refund(Varien_Object $payment, $amount)
	{
		
		try {
			$transaction = $this->getHelper()->loadTransactionByPayment($payment->getId());
			$order = $payment->getOrder();
			Customweb_PayboxCw_Model_ConfigurationAdapter::setStore($order);
			$adapter = $this->getHelper()->createContainer()->getBean('Customweb_Payment_BackendOperation_Adapter_Service_IRefund');

			$refunded = false;
			if ($transaction->getTransactionObject()->isRefundPossible() && Mage::registry('cw_payboxcw_refund_update') !== true) {
				$isPartialRefund = $amount != $transaction->getTransactionObject()->getTransactionContext()
					->getOrderContext()
					->getOrderAmountInDecimals();
				if ($isPartialRefund) {
					if ($transaction->getTransactionObject()->isPartialRefundPossible()) {
						$items = Customweb_Util_Invoice::getItemsByReductionAmount($transaction->getTransactionObject()->getTransactionContext()->getOrderContext()->getInvoiceItems(),
							$amount,
							$transaction->getTransactionObject()->getCurrencyCode()
						);
						$adapter->partialRefund($transaction->getTransactionObject(), $items, false);
					} else {
						Mage::log("Method::refund() : No partial refund possible. Transaction log : " . print_r($transaction->getTransactionObject()->getErrorMessages(), true));
						Mage::throwException($this->getHelper()
							->__('Partial refund not possible. You may retry with the total transaction amount.'));
					}
				} else {
					$adapter->refund($transaction->getTransactionObject());
				}

				$transaction->save();

				$refunded = true;
			}

			if ($refunded || Mage::registry('cw_payboxcw_refund_update') === true) {
				$payment->setIsTransactionClosed(true);
				$refunds = $transaction->getTransactionObject()->getRefunds();
				$refund = end($refunds);
				$payment->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, array(
					'TRANSACTIONID' => $transaction->getTransactionObject()->getPaymentId(),
					'REFUNDID' => $refund->getRefundId(),
					'AMOUNT' => $refund->getAmount(),
					'AMT' => -$refund->getAmount(),
					'STATUS' => $refund->getStatus()
				));
				$this->_generateTransactionId($payment, Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND);
				return $this;
			} else {
				//Mage::log("Method::refund() : No refund possible. Transaction log : " . print_r($transaction->getTransactionObject()->getErrorMessages(), true));
				Mage::throwException($this->getHelper()->__('No refund possible.'));
			}
		} catch (Exception $e) {
			Mage::log("Exception in Method::refund() : " . $e->getMessage());
			Mage::getSingleton('core/session')->addError($e->getMessage());
			throw $e;
		}
		
		
	}

	/**
	 * The transaction is captured with the specified amount.
	 *
	 * @param Varien_Object $payment The payment object containing all the payment specific data for the order.
	 * @param unknown_type $amount The amount to capture. This may be less than the amount of the order but not more.
	 * @return Customweb_PayboxCw_Model_Method
	 */
	public function capture(Varien_Object $payment, $amount)
	{
		
		$invoice = Mage::registry('current_invoice');
		try {
			$transaction = $this->getHelper()->loadTransactionByPayment($payment->getId());

			$order = $payment->getOrder();
			Customweb_PayboxCw_Model_ConfigurationAdapter::setStore($order);
			if ($invoice instanceof Mage_Sales_Model_Order_Invoice) {
				$adapter = $this->getHelper()->createContainer()->getBean('Customweb_Payment_BackendOperation_Adapter_Service_ICapture');
				if ($transaction->getTransactionObject()->isCapturePossible()) {
					if ($transaction->getTransactionObject()->isPartialCapturePossible()) {
						$adapter->partialCapture($transaction->getTransactionObject(), $this->getInvoiceItems($invoice), false);
					} else {
						$adapter->capture($transaction->getTransactionObject());
					}
					$transaction->save();
				}
			}

			if ($transaction->getTransactionObject()->isCaptured()) {
				$payment->setIsTransactionClosed(false);
				$captures = $transaction->getTransactionObject()->getCaptures();
				$capture = end($captures);
				$payment->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, array(
					'TRANSACTIONID' => $transaction->getTransactionObject()->getPaymentId(),
					'CAPTUREID' => $capture->getCaptureId(),
					'AMOUNT' => $capture->getAmount(),
					'AMT' => $capture->getAmount(),
					'STATUS' => $capture->getStatus()
				));
				$this->_generateTransactionId($payment, Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
				return $this;
			} else {
				Mage::log("Capture failed. Transaction error messages : " . print_r($transaction->getTransactionObject()->getErrorMessages(), true));
				$messages = $transaction->getTransactionObject()->getErrorMessages();
				Mage::throwException($this->getHelper()->__('The invoice could not be captured and processed. Reason: ') . end($messages));
			}
		} catch (Exception $e) {
			Mage::log("Exception in Method::capture() : " . $e->getMessage());
			Mage::getSingleton('core/session')->addError($e->getMessage());
			throw $e;
		}
		
		
	}
	
	public function processInvoice($invoice, $payment)
	{
		parent::processInvoice($invoice, $payment);
	
		// Make sure the order is in the desired state
		$transaction = $this->getHelper()->loadTransactionByPayment($payment->getId());
		$order = $payment->getOrder();
		$this->setOrderStatus($order, $transaction->getTransactionObject()->getOrderStatus());
	}
	
	protected function getInvoiceItems($invoice)
	{
		/* @var $invoice Mage_Sales_Model_Order_Invoice */
		
		$resultItems = array();
		$items = $invoice->getAllItems();

		foreach ($items as $item) {
			$orderItem = $item->getOrderItem();
    		if ($orderItem->getParentItemId() != null && $orderItem->getParentItem()
    			->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
    			continue;
    		}
    		if ($orderItem->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $orderItem->getParentItemId() == null) {
    			continue;
    		}

			$productItem = $this->getHelper()->getProductItem($item);
			if ($productItem) {
				$resultItems[] = $productItem;
			}

			$discountItem = $this->getHelper()->getDiscountItem($item);
			if ($discountItem) {
				$resultItems[] = $discountItem;
			}
		}
		
		$surchargeItem = $this->getHelper()->getFoomanSurchargeItem($invoice->getOrder());
		if ($surchargeItem) {
			$resultItems[] = $surchargeItem;
		}

		$shippingItem = $this->getHelper()->getShippingItem($invoice);
		if ($shippingItem) {
			$resultItems[] = $shippingItem;
		}
		
		$resultItems = Customweb_Util_Invoice::ensureUniqueSku($resultItems);
		
		$event = new StdClass;
		$event->items = array();
		Mage::dispatchEvent('customweb_collect_invoice_items', array(
			'invoice' => $invoice,
			'result' => $event
		));
		
		foreach ($event->items as $item) {
			$resultItems[] = new Customweb_Payment_Authorization_DefaultInvoiceItem($item['sku'], $item['name'], $item['taxRate'], $item['amountIncludingTax'], $item['quantity'], $item['type']);
		}
		
		$currencyCode = $invoice->getOrder()->getOrderCurrency()->getCode();
		$adjustmentItem = $this->getHelper()->getAdjustmentItem($resultItems, $invoice->getGrandTotal(), $currencyCode);
		if ($adjustmentItem) {
			$resultItems[] = $adjustmentItem;
		}
		
		return $resultItems;
	}

	/**
	 * This method does post processing of successfull payments. Order and quote states are
	 * set and and invoice is generated (and captured) according to the settings in the backend.
	 *          	   	  	 	  
	 * Attention: This method is called from within redirectAction  (access to checkout session)
	 * and from within processAction (no access to checkout session)
	 *
	 * @param Mage_Sales_Model_Order $order
	 * @param string $transactionId
	 */
	public function processPayment(Mage_Sales_Model_Order $order, Customweb_PayboxCw_Model_Transaction $transaction)
	{
		if ($order->getId()) {
			Customweb_PayboxCw_Model_ConfigurationAdapter::setStore($order);
			if ($transaction->getTransactionObject()->isAuthorized()) {
				$transaction->saveIgnoreOrderStatus();
				$order->setState(self::STATE_PROCESSING);
				$order->save();
				
				Mage::dispatchEvent('payboxcw_payment_success', array(
					'order' => $order
				));

				$quote = Mage::getModel('sales/quote');
				$quote->load($order->getQuoteId());

				$quote->setIsActive(1);

				if ($transaction->getTransactionObject()->isAuthorizationUncertain()) {
					$order->addStatusHistoryComment($this->getHelper()->__('Authorization is uncertain.') . "\n<br>PaymentId:" . $transaction->getTransactionObject()->getPaymentId());
					$order->save();
					$this->createAuthPaymentTransaction($order, $transaction);
				} else {
					$order->addStatusHistoryComment($this->getHelper()->__('Customer successfully returned from Paybox') . "\n<br>PaymentId:" . $transaction->getTransactionObject()->getPaymentId());
					$order->save();
					$this->createAuthPaymentTransaction($order, $transaction);
					if ($this->createInvoice($order, $transaction)) {
						Mage::log("Method::processPayment(): Invoice was created successfully.");
						$order->addStatusHistoryComment($this->getHelper()->__('Invoice was created successfully'));
						$order->save();
						
						Mage::dispatchEvent('payboxcw_invoice_created', array(
							'order' => $order
						));
					}
				}
				
				$quote->setIsActive(0);
				$quote->save();
				
				if ($transaction->getTransactionObject()->getTransactionContext()->isSendOrderEmail()) {
					$order->sendNewOrderEmail();
				}

				Mage::log("Method::processPayment(): Payment processed successfully.");
			} else {
				Mage::log("Method::processPayment(): Transaction is not authorized.");
			}

			$order->save();
		} else {
			Mage::log("Method::processPayment(): Order is not available.");
		}
	}
	
	protected function createAuthPaymentTransaction(Mage_Sales_Model_Order $order, Customweb_PayboxCw_Model_Transaction $transaction)
	{
		$payment = $order->getPayment();
			
		$paymentId = $transaction->getTransactionObject()->getPaymentId();
		if ($paymentId === null) {
			throw new Exception("No paymentId provided.");
		}
			
		$payment->setTransactionId($paymentId)
			->setParentTransactionId(null)
			->setIsTransactionClosed(false);
		$payment->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, array(
			'TRANSACTIONID' => $transaction->getTransactionObject()->getPaymentId(),
			'AMOUNT' => $transaction->getTransactionObject()->getAuthorizationAmount(),
			'AMT' => $transaction->getTransactionObject()->getAuthorizationAmount(),
			'CURRENCY' => $transaction->getTransactionObject()->getCurrencyCode(),
			'AUTHORIZATIONMETHOD' => $transaction->getTransactionObject()->getAuthorizationMethod()
		));
		$paymentTransaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);
		$paymentTransaction->save();
		
		$payment->setTransactionId(null);
	}

	/**
	 * Depending on the settings we create an invoice and capture it.
	 *
	 * @param Mage_Sales_Model_Order $order
	 * @param boolean Returns true if an invoice has been created.
	 */
	protected function createInvoice(Mage_Sales_Model_Order $order, Customweb_PayboxCw_Model_Transaction $transaction)
	{
		try {
			$isSettlementAfterOrder = $this->getPaymentMethodConfigurationValue('settlement');
			$isCaptureAfterOrder = $transaction->getTransactionObject()->isCaptured();

			Mage::log("Payment config: settlement='" . $isSettlementAfterOrder . "' capturing='" . $isCaptureAfterOrder . "'");

			if ($isSettlementAfterOrder != 'settlement_direct') {
				return false;
			}
			// Do not create multiple invoices
			if ($order->hasInvoices()) {
				return false;
			}

			$invoice = $order->prepareInvoice();
			if ($isCaptureAfterOrder) {
				$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
			}

			$invoice->register();
			$invoice->setTransactionId($transaction->getTransactionObject()->getPaymentId());

			$transactionSave = Mage::getModel('core/resource_transaction')->addObject($invoice)
				->addObject($invoice->getOrder());
			$transactionSave->save();

			return true;

		} catch (Exception $e) {
			Mage::log("Create Invoice: " . $e->getMessage());
			return false;
		}
	}

	/**
	 *  Return Order Place Redirect URL
	 *          	   	  	 	  
	 *  @return	  string Order Redirect URL
	 */
	public function getOrderPlaceRedirectUrl()
	{
		$adapter = $this->getAuthorizationAdapter(true);

		$quote = Mage::getModel('checkout/cart')->getQuote();
		$quote->setIsActive(true);
		$quote->save();

		if ($adapter instanceof Customweb_Payment_Authorization_PaymentPage_IAdapter) {
			return Mage::getUrl('PayboxCw/process/ppRedirect', array(
				'transaction_id' => Mage::registry('cstrxid'),
				'_secure' => true
			));
		} elseif ($adapter instanceof Customweb_Payment_Authorization_Iframe_IAdapter
			|| $adapter instanceof Customweb_Payment_Authorization_Widget_IAdapter) {
			return Mage::getUrl('PayboxCw/checkout/pay', array(
				'transaction_id' => Mage::registry('cstrxid'),
				'_secure' => true
			));
		} else {
			return Mage::getUrl('PayboxCw/process/dummy', array(
				'transaction_id' => Mage::registry('cstrxid'),
				'_secure' => true
			));
		}
	}

	public function generateHiddenFormParameters(Customweb_PayboxCw_Model_Transaction $transaction)
	{
		$adapter = $this->getAuthorizationAdapter(true, $transaction);
		$hiddenFields = $adapter->getHiddenFormFields($transaction->getTransactionObject());
		$actionUrl = $adapter->getFormActionUrl($transaction->getTransactionObject());
		$transaction->save();

		$jsonObject = array();
		$jsonObject['cstrxid'] = $transaction->getTransactionObject()->getTransactionContext()->getTransactionId();
		$jsonObject['actionUrl'] = $actionUrl;
		$jsonObject['fields'] = $hiddenFields;
		return json_encode($jsonObject);
	}

	private function loadFailedTransaction()
	{
		$failedTransactionId = Mage::getSingleton('checkout/session')->getData('failed_transaction_' . $this->getCode());
		Mage::getSingleton('checkout/session')->setData('failed_transaction_' . $this->getCode(), "");
		if ($failedTransactionId > 0) {
			Mage::log("Load failed transaction" . $failedTransactionId);
			$failedTransaction = $this->getHelper()->loadTransaction($failedTransactionId);
			$currentCustomerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
			if ($failedTransaction->getCustomerId() == $currentCustomerId) {
				return $failedTransaction->getTransactionObject();
			}
		}
		return null;
	}

	protected function getFormFields(array $parameters) {
		if (!isset(self::$formFields[$this->getCode()])) {
			$adapter = $this->getAuthorizationAdapter(false);
			$customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
			$failedTransaction = $this->loadFailedTransaction();
			$paymentCustomerContext = $this->getHelper()->getPaymentCustomerContext($customerId);

			$formFields = array();
			if (!empty($parameters['alias_id']) && $parameters['alias_id'] != 'new') {
				$aliasTransactionId = $parameters['alias_id'];
				$aliasTransaction = $this->getHelper()->loadTransaction($aliasTransactionId);
				if ($aliasTransaction->getCustomerId() == $customerId) {
					Mage::getSingleton('checkout/session')->setAliasId($aliasTransactionId);

					$formFields = $adapter->getVisibleFormFields($this->getOrderContext(false), $aliasTransaction->getTransactionObject(), $failedTransaction, $paymentCustomerContext);
				}
			} else {
				Mage::getSingleton('checkout/session')->setAliasId('new');
				$formFields = $adapter->getVisibleFormFields($this->getOrderContext(false), null, $failedTransaction, $paymentCustomerContext);
			}
			$paymentCustomerContext->persist();
			self::$formFields[$this->getCode()] = $formFields;
		}

		return self::$formFields[$this->getCode()];
	}

	public function generateVisibleFormFields(array $parameters)
	{
		return $this->getFormRenderer()->renderElementsWithoutJavaScript($this->getFormFields($parameters));
	}

	public function generateFormJavaScript(array $parameters)
	{
		return $this->getFormRenderer()->renderElementsJavaScript($this->getFormFields($parameters));
	}

	public function getFormRenderer()
	{
		$renderer = new Customweb_PayboxCw_Model_FormRenderer();
		$renderer->setNameSpacePrefix($this->getCode());
		return $renderer;
	}

	public function generateJavascriptForAjax(Customweb_PayboxCw_Model_Transaction $transaction)
	{
		$adapter = $this->getAuthorizationAdapter(true, $transaction);

		if ($adapter instanceof Customweb_Payment_Authorization_Ajax_IAdapter) {
			$fileUrl = $adapter->getAjaxFileUrl($transaction->getTransactionObject());
			$function = $adapter->getJavaScriptCallbackFunction($transaction->getTransactionObject());

			$transaction->save();

			if ($transaction->getTransactionObject()->isAuthorizationFailed()) {
				$data = array(
					'error' => 'yes',
					'error_message' => $this->getErrorMessageToDisplay($transaction)
				);
				return $this->generateJson($data);
			}
			$data = array(
				'error' => 'no',
				'javascriptUrl' => $fileUrl,
				'callbackFunction' => $function
			);
			return $this->generateJson($data);
		} else {
			$data = array(
				'error' => 'yes',
				'error_message' => $this->getHelper()->__("Ajax authorization not supported.")
			);
			return $this->generateJson($data);
		}

	}

	public function isPaymentFailed(Mage_Sales_Model_Order $order)
	{
		$transaction = $this->getHelper()->loadTransactionByOrder($order->getId());
		return $transaction->getTransactionObject()->isAuthorizationFailed();
	}

	public function isPaymentAuthorized(Mage_Sales_Model_Order $order)
	{
		$transaction = $this->getHelper()->loadTransactionByOrder($order->getId());
		return $transaction->getTransactionObject()->isAuthorized();
	}

	public function fail(Customweb_PayboxCw_Model_Transaction $transaction, array $parameters)
	{
		$order = $transaction->getOrder();
		
		Mage::dispatchEvent('payboxcw_payment_fail', array(
			'order' => $order
		));
		
		$session = Mage::getSingleton('checkout/session');
		if ($order->getState() != Mage_Sales_Model_Order::STATE_CANCELED && $session->getData('failed_transaction_' . $this->getCode()) != $transaction->getTransactionId()) {
			$session->setData('failed_transaction_' . $this->getCode(), $transaction->getTransactionId());
			Mage::log("Set failed transaction " . 'failed_transaction_' . $this->getCode() . " to id " . $transaction->getTransactionId());
			$message = $this->getErrorMessageToDisplay($transaction);

			Mage::getSingleton('core/session')->addError($this->getHelper()->__($message));

			$order->registerCancellation($message);
			//$order->setIsActive(0);
			$this->setOrderStatus($order, Customweb_PayboxCw_Model_Method::PAYBOXCW_STATUS_CANCELED, $this->getHelper()
					->__('Payment could not be authorized. Cancelling order. Reason : ') . $message);

			$quote = Mage::getModel('sales/quote')->load($order->getQuoteId());

			if ($quote->getId()) {
				$quote->setIsActive(1)
					->setReservedOrderId(NULL)
					->save();
				$session->replaceQuote($quote);
			}

			$session->unsLastRealOrderId();

			Mage::getSingleton('checkout/session')->replaceQuote($quote);
		}
	}

	public function success(Customweb_PayboxCw_Model_Transaction $transaction, array $parameters)
	{
		$quote = Mage::getModel('sales/quote');
		$quote->load($transaction->getOrder()->getQuoteId());

		$quote->setIsActive(0);
		$quote->save();
	}

	public function pullUpdate(Customweb_PayboxCw_Model_Transaction $transaction)
	{
		
	}

	public function redirectToPaymentPage(Customweb_PayboxCw_Model_Transaction $transaction, array $parameters)
	{
		$info = $this->getInfoInstance();
		$additionalFormData = $info->getAdditionalInformation();
		$parameters = array_merge($additionalFormData, $parameters);
	
		$adapter = $this->getAuthorizationAdapter(true, $transaction);
		$isHeaderRedirect = $adapter->isHeaderRedirectionSupported($transaction->getTransactionObject(), $parameters);
		if ($isHeaderRedirect) {
			$url = $adapter->getRedirectionUrl($transaction->getTransactionObject(), $parameters);
			$transaction->save();
			header('Location: ' . $url);
			exit;
		} else {
			$html = $this->getFormHtml($adapter, $transaction, true, '', $parameters);
			echo $html;
			exit;
		}
	}

	public function getFormHtml($adapter, Customweb_PayboxCw_Model_Transaction $transaction, $javaScript = true, $additionalFields = "", $formData = null)
	{
		if ($formData === null) {
			$formData = $_REQUEST;
		}

		$formActionUrl = $adapter->getFormActionUrl($transaction->getTransactionObject(), $formData);
		$hiddenFields = $adapter->getParameters($transaction->getTransactionObject(), $formData);
		$transaction->save();
		$html = '
			<form action="' . $formActionUrl . '" method="POST" name="process_form">';

		$html .= Customweb_Util_Html::buildHiddenInputFields($hiddenFields);

		$html .= $additionalFields;

		$html .= '<button title="' . $this->getHelper()
			->__("Continue") . '" type="submit" class="scalable go" style="">
				<span>
					<span>
						<span>' . $this->getHelper()
			->__("Continue") . '</span>
					</span>
				</span>
			</button>
		</form>';
		if ($javaScript) {
			$html .= '<script type="text/javascript">
			window.onload = function(){
				document.process_form.submit();
			};
			</script>';
		}

		return $html;
	}

	public function startMotoAuthorization(Mage_Sales_Model_Order $order, array $parameters)
	{
		Customweb_PayboxCw_Model_ConfigurationAdapter::setStore($order);
		$adapter = $this->getHelper()->getAuthorizationAdapter(Customweb_Payment_Authorization_Moto_IAdapter::AUTHORIZATION_METHOD_NAME);
		$orderContext = Customweb_PayboxCw_Model_OrderContext::fromOrder($order);

		if ($adapter->isAuthorizationMethodSupported($orderContext)) {
			$paymentCustomerContext = $this->getHelper()->getPaymentCustomerContext($order->getCustomerId());
			try {
				$adapter->validate($orderContext, $paymentCustomerContext, $parameters);
				$transaction = $this->createTransaction($order, true, $adapter);
				$html = $this->generateMotoForm($order, $transaction, $parameters);
				return $html;
			} catch (Exception $e) {
				Mage::log($this->getCode() . ' Method::startMotoAuthorization() Failed because "' . $e->getMessage() . '"');
			}
			$paymentCustomerContext->persist();
		}
	}
	
	public function generateMotoForm(Mage_Sales_Model_Order $order, Customweb_PayboxCw_Model_Transaction $transaction, $parameters)
	{
		$adapter = $this->getHelper()->getAuthorizationAdapter(Customweb_Payment_Authorization_Moto_IAdapter::AUTHORIZATION_METHOD_NAME);
		$failedTransaction = $this->loadFailedTransaction();
		$paymentCustomerContext = $this->getHelper()->getPaymentCustomerContext($order->getCustomerId());
	
		$formFields = $adapter->getVisibleFormFields($transaction->getTransactionObject()->getTransactionContext()->getOrderContext(), null, $failedTransaction, $paymentCustomerContext);
		$formActionUrl = $adapter->getFormActionUrl($transaction->getTransactionObject(), $parameters);
		$hiddenFields = $adapter->getParameters($transaction->getTransactionObject(), $parameters);
	
		$paymentCustomerContext->persist();
		$transaction->save();
	
		$form = new Customweb_Payment_BackendOperation_Form();
		$form->setMachineName('moto_form');
		$form->setTargetUrl($formActionUrl);
		$form->setRequestMethod('POST');
		$form->setTitle($this->getHelper()->__('Mail order/telephone order authorization'));
		foreach ($formFields as $formField) {
			$form->addElement($formField);
		}
		foreach ($hiddenFields as $key => $value) {
			if (is_array($value)) {
				foreach ($value as $val) {
					$form->addElement(new Customweb_Form_HiddenElement(new Customweb_Form_Control_HiddenInput($key. '[]', $val)));
				}
			} else {
				$form->addElement(new Customweb_Form_HiddenElement(new Customweb_Form_Control_HiddenInput($key, $value)));
			}
		}
		$button = new Customweb_Form_Button();
		$button->setMachineName('submit');
		$button->setTitle($this->getHelper()->__('Continue'));
		$form->addButton($button);
	
		$renderer = new Customweb_PayboxCw_Model_BackendFormRenderer();
		$renderer->setShowScope(false);
		$result = $renderer->renderForm($form);
		return $result;
	}

	public function getIFrameUrl(Customweb_PayboxCw_Model_Transaction $transaction, array $parameters)
	{
		$adapter = $this->getAuthorizationAdapter(true, $transaction);
		$url = "";

		if ($adapter instanceof Customweb_Payment_Authorization_Iframe_IAdapter) {
			$url = $adapter->getIframeUrl($transaction->getTransactionObject(), $parameters);
		}

		$transaction->save();
		return $url;
	}

	public function getIFrameHeight(Customweb_PayboxCw_Model_Transaction $transaction, array $parameters)
	{
		$adapter = $this->getAuthorizationAdapter(true, $transaction);
		$height = 1000;

		if ($adapter instanceof Customweb_Payment_Authorization_Iframe_IAdapter) {
			$height = $adapter->getIframeHeight($transaction->getTransactionObject(), $parameters);
		}

		$transaction->save();
		return $height;
	}

	public function getWidgetHtml(Customweb_PayboxCw_Model_Transaction $transaction, array $parameters)
	{
		$adapter = $this->getAuthorizationAdapter(true, $transaction);
		$url = "";

		if ($adapter instanceof Customweb_Payment_Authorization_Widget_IAdapter) {
			$url = $adapter->getWidgetHTML($transaction->getTransactionObject(), $parameters);
		}

		$transaction->save();
		return $url;
	}

	public function getPaymentMethodName()
	{
		return $this->paymentMethodName;
	}

	public function getPaymentMethodDisplayName()
	{
		return $this->getPaymentMethodConfigurationValue('title');
	}

	public function getPaymentMethodConfigurationValue($key, $languageCode = null)
	{
		$result = null;
		$rs = Mage::getStoreConfig('payment/' . $this->getCode() . '/' . $key, Customweb_PayboxCw_Model_ConfigurationAdapter::getStoreId());

		$multiSelectKeys = $this->getMultiSelectKeys();
		$fileKeys = $this->getFileKeys();

		if (isset($multiSelectKeys[$key])) {
			$result = empty($rs) ? array() : explode(',', $rs);
		} elseif (isset($fileKeys[$key])) {
			$defaultValue = $fileKeys[$key];
			if (empty($rs) || $defaultValue == $rs) {
				return Mage::helper('PayboxCw')->getAssetResolver()->resolveAssetStream($defaultValue);
			} else {
				return new Customweb_Core_Stream_Input_File(Mage::getBaseDir('media') . '/payboxcw/setting/' . $this->getPaymentMethodName() . '/' . $key . '/' . $rs);
			}
		} else {
			$result = $rs;
		}

		return $result;
	}

	public function existsPaymentMethodConfigurationValue($key, $languageCode = null)
	{
		// Make sure the order status is not set on cancel. This does not really work in magento.
		if ($key == 'status_cancelled') {
			return false;
		}
		return null != Mage::getStoreConfig('payment/' . $this->getCode() . '/' . $key, Customweb_PayboxCw_Model_ConfigurationAdapter::getStoreId());
	}

	abstract protected function getMultiSelectKeys();
	
	abstract protected function getFileKeys();

	/**
	 *          	   	  	 	  
	 * @return NULL
	 */
	public function getAuthorizationAdapter($isOrderAvailable, Customweb_PayboxCw_Model_Transaction $transaction = null)
	{
		if ($this->authorizationAdapter == null) {
			if ($this->authorizationMethodName != null) {
				$this->authorizationAdapter = $this->getHelper()->getAuthorizationAdapter($this->authorizationMethodName);
			} else {
				if ($isOrderAvailable == true) {
					$order = Mage::getModel('sales/order');
					$order->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
					Customweb_PayboxCw_Model_ConfigurationAdapter::setStore($order);
				}
	
				if ($transaction && $transaction !== null && $transaction->getTransactionObject() !== null && $transaction->getTransactionObject()->getTransactionContext()->isMotoTransaction()) {
					$this->authorizationAdapter = $this->getHelper()->getAuthorizationAdapter(Customweb_Payment_Authorization_Moto_IAdapter::AUTHORIZATION_METHOD_NAME);
				} else {
					$this->authorizationAdapter = $this->getHelper()->getAuthorizationAdapterByContext($this->getOrderContext($isOrderAvailable));
				}
			
				$this->authorizationMethodName = $this->authorizationAdapter->getAuthorizationMethodName();
			}
		}
		return $this->authorizationAdapter;
	}

	public function getOrderContext($isOrderAvailable)
	{
		if ($this->orderContext == null) {
			$orderContext = new Customweb_PayboxCw_Model_OrderContext($this, $isOrderAvailable);
		
			$result = new StdClass;
			$result->orderContext = $orderContext;
			Mage::dispatchEvent('customweb_payment_create_order_context', array(
				'result' => $result,
				'payment_method' => $this
			));
			
			$this->orderContext = $result->orderContext;
		}
		
		return $this->orderContext;
	}

	/**
	 * @return Customweb_PayboxCw_Model_PaymentCustomerContext
	 */
	protected function getCustomerContext()
	{
		$customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();

		return new Customweb_PayboxCw_Model_PaymentCustomerContext($customerId);
	}

	/**
	 * @param Mage_Sales_Model_Order $order
	 * @return Customweb_PayboxCw_Model_Transaction
	 */
	public function createTransaction(Mage_Sales_Model_Order $order, $moto = false)
	{
		$transaction = Mage::getModel('payboxcw/transaction');
		$transaction->setOrderId($order->getId());
		$transaction->setOrderPaymentId($order->getPayment()->getId());
		$transaction->save();

		if ($moto) {
			$transactionContext = $this->getMotoTransactionContext($order, $transaction);
			$adapter = $this->getHelper()->getAuthorizationAdapter(Customweb_Payment_Authorization_Moto_IAdapter::AUTHORIZATION_METHOD_NAME);
		} else {
			$transactionContext = $this->getTransactionContext($order, $transaction);
			$adapter = $this->getAuthorizationAdapter(true);
		}

		// TODO implement failed transaction recovery (second param)
		$transactionObject = $adapter->createTransaction($transactionContext, null);
		
		$transaction->setTransactionObject($transactionObject);
		$transaction->save();
		
		return $transaction;
	}

	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param Customweb_PayboxCw_Model_Transaction $transaction
	 * @param string $backendSuccessUrl
	 * @param string $backendFailUrl
	 * @return Customweb_PayboxCw_Model_TransactionContext
	 */
	public function getTransactionContext(Mage_Sales_Model_Order $order, Customweb_PayboxCw_Model_Transaction $transaction, $backendSuccessUrl = '', $backendFailUrl = '')
	{
		$aliasTransactionId = Mage::getSingleton('checkout/session')->getAliasId();
		$storeId = $order->getStore()->getId();
		$orderContext = Customweb_PayboxCw_Model_OrderContext::fromOrder($order);
		$transactionContext = new Customweb_PayboxCw_Model_TransactionContext($orderContext, $order->getIncrementId(), $transaction->getTransactionId(), $this->getCustomerContext(), $aliasTransactionId, $backendSuccessUrl, $backendFailUrl, $storeId);

		$result = new StdClass;
		$result->transactionContext = $transactionContext;
		Mage::dispatchEvent('customweb_payment_create_transcation_context', array(
			'result' => $result,
			'order' => $order
		));

		return $result->transactionContext;
	}

	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param Customweb_PayboxCw_Model_Transaction $transaction
	 * @param string $backendSuccessUrl
	 * @param string $backendFailUrl
	 * @return Customweb_PayboxCw_Model_TransactionContext
	 */
	public function getMotoTransactionContext(Mage_Sales_Model_Order $order, Customweb_PayboxCw_Model_Transaction $transaction)
	{
		$storeId = $order->getStore()->getId();
		$orderContext = Customweb_PayboxCw_Model_OrderContext::fromOrder($order);

		$backendSuccessUrl = Mage::getModel('adminhtml/url')->getUrl('adminhtml/motopayboxcw/success', array(
			'order_id' => $order->getId()
		));
		$backendFailUrl = Mage::getModel('adminhtml/url')->getUrl('adminhtml/motopayboxcw/fail', array(
			'order_id' => $order->getId()
		));

		$transactionContext = new Customweb_PayboxCw_Model_TransactionContext($orderContext, $order->getIncrementId(), $transaction->getId(), $this->getCustomerContext(), null, $backendSuccessUrl, $backendFailUrl, $storeId);
		$transactionContext->setMotoTransaction(true);

		$result = new StdClass;
		$result->transactionContext = $transactionContext;
		Mage::dispatchEvent('customweb_payment_create_transcation_context', array(
		'result' => $result,
		'order' => $order
		));

		return $result->transactionContext;
	}

	/**
	 * @return Customweb_PayboxCw_Helper_Data
	 */
	public function getHelper()
	{
		if ($this->helper == null) {
			$this->helper = Mage::helper('PayboxCw');
		}
		return $this->helper;
	}

	/**
	 *
	 * @param Customweb_Payment_Authorization_ITransaction $transaction
	 * @return string Error message or null
	 */
	protected function getErrorMessageToDisplay(Customweb_PayboxCw_Model_Transaction $transaction)
	{
		$errorMessages = $transaction->getTransactionObject()->getErrorMessages();
		$messageToDisplay = end($errorMessages);
		reset($errorMessages);
		if (empty($messageToDisplay)) {
			$messageToDisplay = 'There has been a problem during the processing of your payment.';
		}
		return nl2br($messageToDisplay);
	}

	public function loadAliasForCustomer()
	{
		$result = array();

		if (Mage::helper('customer')->isLoggedIn()) {
			$customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();

			$collection = Mage::getModel('payboxcw/transaction')->getCollection()
				->addFieldToFilter('customer_id', array(
					'eq' => $customerId
				))
				->addFieldToFilter('payment_method', array(
					'eq' => $this->getCode()
				))
				->addFieldToFilter('alias_active', array(
					'eq' => 1
				))
				->addFieldToFilter('alias_for_display', array(
					'neq' => 'NULL'
				));

			foreach ($collection as $alias) {
				$result[$alias->getTransactionId()] = $alias->getAliasForDisplay();
			}
		}
		return $result;
	}

	public function loadAlias($aliasTransactionId)
	{
		return $this->getHelper()->loadTransaction($aliasTransactionId);
	}

	/**
	 * Generates a JSON object from an array. Array values are writen as strings "value" to the JSON
	 * object with the exceptions of values with a key that ends on "Function" those are stored
	 * directly.
	 * @param array $array | string
	 * @return string
	 */
	protected function generateJson($array)
	{
		if (!is_array($array)) {
			return $array;
		} else {
			$first = true;
			$result = '{';
			foreach ($array as $key => $value) {
				if (!$first) {
					$result .= ',';
				}
				$delim = '"';
				if (substr($key, -strlen('Function')) === 'Function') {
					$delim = ' ';
				}
				$result .= $key . ': ' . $delim . $this->generateJson($value) . $delim;
				$first = false;
			}

			$result .= '}';
			return $result;
		}
	}
}
