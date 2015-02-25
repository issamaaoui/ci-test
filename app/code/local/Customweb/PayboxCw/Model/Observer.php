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

class Customweb_PayboxCw_Model_Observer
{
	private $timeout = 0;

	public function initCart(Varien_Event_Observer $observer)
	{
		if (Mage::getStoreConfig('payboxcw/general/cancel_existing_orders')) {
			$cart = $observer->getCart();
			$customer = Mage::getSingleton('customer/session')->getCustomer();

			$query = 'SELECT product_id FROM sales_flat_quote_item WHERE quote_id = ' . $cart->getQuote()->getId();
			$resource = Mage::getSingleton('core/resource');
			$conn = $resource->getConnection('core_read');
			$productIds = $conn->query($query)->fetchAll();

			$orders = Mage::getResourceModel('sales/order_collection')
				->addAttributeToSelect('*')
				->addAttributeToFilter('customer_id', $customer->getId())
				->addAttributeToFilter('status', Customweb_PayboxCw_Model_Method::PAYBOXCW_STATUS_PENDING)
				->load();

			if (count($orders) > 0 && count($productIds) > 0) {
				foreach ($productIds as $productId) {
					$product = Mage::getModel('catalog/product')->load($productId);
					if (!$product->isSalable()) {
						foreach ($orders as $order) {
							$order->cancel();

							$order->setIsActive(0);
							$order->addStatusToHistory(Customweb_PayboxCw_Model_Method::PAYBOXCW_STATUS_CANCELED, Mage::helper('PayboxCw')->__('Order cancelled, because the customer was too long in the payment process of Paybox.'));
							$order->save();
						}
						break;
					}
				}
			}
		}
	}

	public function saveOrder(Varien_Event_Observer $observer)
	{
		$order = $observer->getOrder();
		try {
			if (strpos($order->getPayment()->getMethodInstance()->getCode(), 'payboxcw') === 0) {
				Mage::register('cw_order_id', $order->getId());
				
				if (Mage::registry('cw_is_moto') == null) {
					$transaction = $order->getPayment()->getMethodInstance()->createTransaction($order);
					Mage::register('cstrxid', $transaction->getTransactionId());
				}
			}
		} catch (Exception $e) {}
	}

	public function capturePayment(Varien_Event_Observer $observer)
	{
		
	}

	public function cancelOrder(Varien_Event_Observer $observer)
	{
		$order = $observer->getOrder();
		if (strpos($order->getPayment()->getMethodInstance()->getCode(), 'payboxcw') === 0) {
			$order->addStatusHistoryComment(Mage::helper('PayboxCw')->__('Transaction cancelled successfully'));
		}
	}

	public function invoiceView(Varien_Event_Observer $observer)
	{
		$block = $observer->getBlock();
		$invoice = $observer->getInvoice();

		if (strpos($invoice->getOrder()->getPayment()->getMethodInstance()->getCode(), 'payboxcw') === 0) {
			$transaction = Mage::helper('PayboxCw')->loadTransactionByOrder($invoice->getOrder()->getId());

			if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/edit')
				&& $invoice->canCapture()
				&& $transaction->getTransactionObject()->isCapturePossible()
				&& $transaction->getTransactionObject()->isPartialCapturePossible()) {
				$block->addButton('edit', array(
					'label'     => Mage::helper('sales')->__('Edit'),
					'class'     => 'go',
					'onclick'   => 'setLocation(\''.$block->getUrl('*/editpayboxcw/index', array('invoice_id'=>$invoice->getId())).'\')'
				));
			}
		}
	}
}
