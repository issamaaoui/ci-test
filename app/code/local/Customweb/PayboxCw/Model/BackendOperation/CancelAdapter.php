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
 * @Bean
 */
class Customweb_PayboxCw_Model_BackendOperation_CancelAdapter implements Customweb_Payment_BackendOperation_Adapter_Shop_ICancel
{
	public function cancel(Customweb_Payment_Authorization_ITransaction $transaction)
	{
		$transactionModel = $transaction->getTransactionContext()->getTransactionModel();
		$order = $transactionModel->getOrder();
		$transactionModel->setTransactionObject($transaction);
		$transactionModel->save();

		$invoices = $order->getInvoiceCollection();
		if ($invoices->count() == 1) {
			$invoice = $invoices->getFirstItem();
			if ($invoice->canCancel()) {
				$invoice->cancel();
				$invoice->getOrder()->cancel();

				Mage::getModel('core/resource_transaction')
					->addObject($invoice)
					->addObject($invoice->getOrder())
					->addObject($invoice->getOrder()->getPayment())
					->save();
			}
		}
	}
}
