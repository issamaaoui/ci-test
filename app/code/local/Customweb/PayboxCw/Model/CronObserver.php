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

class Customweb_PayboxCw_Model_CronObserver
{
	public function executeCron()
	{
		try {
			$this->cancelOrders();
			
			$packages = array(
			0 => 'Customweb_Paybox',
 			1 => 'Customweb_Payment_Authorization',
 		);
			$packages[] = 'Customweb_Payment_Update_ScheduledProcessor';
			$cronProcessor = new Customweb_Cron_Processor(Mage::helper('PayboxCw')->createContainer(), $packages);
			$cronProcessor->run();
		} catch (Exception $e) {
			Mage::logException($e);
		}
	}
	
	private function cancelOrders()
	{
		$orders = Mage::getResourceModel('sales/order_collection')
			->addAttributeToSelect('*')
			->addAttributeToFilter('status', Customweb_PayboxCw_Model_Method::PAYBOXCW_STATUS_PENDING)
			->load();
		if (count($orders) > 0) {
			$absoluteTime = time() - 7200;
			foreach ($orders as $order) {
				$orderUpdated = strtotime($order->getUpdatedAt());
				if ($absoluteTime >= $orderUpdated) {
					$order->cancel();
					$order->setIsActive(0);
					$order->addStatusToHistory(Customweb_PayboxCw_Model_Method::PAYBOXCW_STATUS_CANCELED, Mage::helper('PayboxCw')->__('Order cancelled, because the customer was too long in the payment process of Paybox.'));
					$order->save();
				}
			}
		}
	}
}