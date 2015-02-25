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

class Customweb_PayboxCw_MotopayboxcwController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * @return Customweb_PayboxCw_Model_Transaction
	 */
	protected function getTransaction()
	{
		if (Mage::registry('cw_transaction') == null || !Mage::registry('cw_transaction')->getId()) {
			$transaction = null;

			$transactionId = $this->getRequest()->getParam('transaction_id');
			$shopTransactionId = $this->getRequest()->getParam('cstrxid');
			$externalTransactionId = $this->getRequest()->getParam('cw_transaction_id');
			$registryTransactionId = Mage::registry('cstrxid');

			if (!empty($transactionId)) {
				$transaction = $this->getHelper()->loadTransaction($transactionId);
			} elseif (!empty($shopTransactionId)) {
				$transaction = $this->getHelper()->loadTransaction($shopTransactionId);
			} elseif (!empty($externalTransactionId)) {
				$transaction = $this->getHelper()->loadTransactionByExternalId($externalTransactionId);
			} elseif (!empty($registryTransactionId)) {
				$transaction = $this->getHelper()->loadTransactionByExternalId($registryTransactionId);
			}

			if ($transaction == null || !$transaction->getId()) {
				Mage::throwException("Transaction was not found.");
			}

			Mage::register('cw_transaction', $transaction);
		}

		return Mage::registry('cw_transaction');
	}

	/**
	 * Return an instance of the helper.
	 *
	 * @return Customweb_PayboxCw_Helper_Data
	 */
	protected function getHelper()
	{
		return Mage::helper('PayboxCw');
	}

	public function processAction()
	{
		$this->loadLayout();

		$this->getLayout()
			->getBlock('head')
			->addCss('css/payboxcw.css');
		$this->getLayout()
			->getBlock('content')
			->append($this->getLayout()
				->createBlock('payboxcw/moto'));

		$this->renderLayout();
	}

	public function successAction()
	{
		$transaction = $this->getTransaction();
		$transaction->getOrder()->getPayment()->getMethodInstance()->success($transaction, $_REQUEST);

		Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Successful payment authorization.'));
		$url = Mage::getModel('adminhtml/url')->getUrl('adminhtml/sales_order/view', array(
				'order_id' => $transaction->getOrder()->getId()
			));
		header('Location: ' . $url);
		exit;
	}

	public function failAction()
	{
		$transaction = $this->getTransaction();
		$transaction->getOrder()->getPayment()->getMethodInstance()->fail($transaction, $_REQUEST);

		Mage::getSingleton('adminhtml/session')->addError($this->__('Failed payment authorization.'));

		$url = Mage::getModel('adminhtml/url')->getUrl('adminhtml/sales_order_create/reorder', array(
				'order_id' => $transaction->getOrder()->getId()
			));
		header('Location: ' . $url);
		exit;
	}
}
