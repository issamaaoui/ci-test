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
 *
 * @category Customweb
 * @package Customweb_PayboxCw
 * @version 1.0.49
 */
class Customweb_PayboxCw_Block_Checkout extends Mage_Core_Block_Template {

	protected function _construct(){
		parent::_construct();
		$this->setTemplate('customweb/payboxcw/checkout.phtml');
	}

	public function getPaymentMethods(){
		$paymentMethods = array();
		$payments = Mage::getSingleton('payment/config')->getActiveMethods();
		foreach ($payments as $paymentCode => $paymentModel) {
			if (preg_match("/payboxcw/i", $paymentCode)) {
				$paymentMethods[] = $paymentCode;
			}
		}
		if (!empty($paymentMethods)) {
			if ($this->getRequest()->getParam('loadFailed') == 'true') {
				Mage::getSingleton('checkout/session')->setStepData('billing', 'allow', true)->setStepData('billing', 'complete', true)->setStepData(
						'shipping', 'allow', true)->setStepData('shipping', 'complete', true)->setStepData('shipping_method', 'allow', true)->setStepData(
						'shipping_method', 'complete', true)->setStepData('payment', 'allow', true);
			}
		}
		return $paymentMethods;
	}

	public function getHiddenFieldsUrl(){
		return Mage::getUrl('PayboxCw/process/getHiddenFields', array(
			'_secure' => true 
		));
	}

	public function getVisibleFieldsUrl(){
		return Mage::getUrl('PayboxCw/process/getVisibleFields', array(
			'_secure' => true 
		));
	}

	public function getAuthorizeUrl(){
		return Mage::getUrl('PayboxCw/process/authorize', array(
			'_secure' => true 
		));
	}

	public function getJavascriptUrl(){
		return Mage::getUrl('PayboxCw/process/ajax', array(
			'_secure' => true 
		));
	}

	public function getSaveShippingMethodUrl(){
		return Mage::getUrl('PayboxCw/onepage/saveShippingMethod', array(
			'_secure' => true 
		));
	}

	public function isPreload(){
		$payments = Mage::getSingleton('payment/config')->getActiveMethods();
		if (!empty($payments)) {
			return true;
		}
		else {
			return false;
		}
	}

	public function getPreloadUrl(){
		if (version_compare(Mage::getVersion(), '1.8', '>=')) {
			return Mage::getUrl('PayboxCw/process/preloadOnepage', array(
				'_secure' => true 
			));
		}
		else {
			return false;
		}
	}
}